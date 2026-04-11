<?php

namespace App\Services\Email;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\EmailUnsubscribe;
use App\Models\Hotel;
use Illuminate\Support\Collection;

/**
 * Ranks customers against a campaign's strategy with no extra LLM call.
 *
 * The heuristic:
 *   1. Customers whose last `hotel_external_id` matches the strategy's
 *      recommended hotel get the highest score (strong intent signal).
 *   2. Customers whose last hotel is in the same city as the recommended
 *      hotel get a medium score.
 *   3. Customers whose ADR is within ±30% of the focus segment's `avg_adr`
 *      get a small bonus (budget match).
 *   4. Ties are broken by `avg_score DESC` then `confirmed_reservations DESC`.
 *
 * Anyone already on the global `email_unsubscribes` list is filtered out.
 */
class RecipientSelector
{
    /**
     * @return Collection<int, Customer>  Ranked customers, distinct by email.
     */
    public function pickForCampaign(Campaign $campaign, int $limit = 50): Collection
    {
        $strategy = $campaign->strategy ?? [];
        $analysis = $campaign->analysis ?? [];

        $recommendedHotelName = $strategy['recommended_hotel']['name'] ?? null;
        $recommendedCity = $strategy['recommended_hotel']['city'] ?? null;
        $targetSegmentName = $analysis['recommended_focus_segment']
            ?? ($strategy['target_segment']['name'] ?? null);

        $focusAdr = null;
        foreach ($analysis['segments'] ?? [] as $segment) {
            if (($segment['name'] ?? null) === $targetSegmentName && isset($segment['avg_adr'])) {
                $focusAdr = (float) $segment['avg_adr'];
                break;
            }
        }

        $recommendedHotelExternalId = null;
        $recommendedHotelCityName = null;
        if ($recommendedHotelName !== null) {
            $hotel = Hotel::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($recommendedHotelName)])
                ->first();
            if ($hotel !== null) {
                $recommendedHotelExternalId = $hotel->external_id;
                $recommendedHotelCityName = $hotel->city_name;
            }
        }
        if ($recommendedHotelCityName === null && $recommendedCity !== null) {
            $recommendedHotelCityName = mb_strtoupper($recommendedCity);
        }

        $peerExternalIds = [];
        if ($recommendedHotelCityName !== null) {
            $peerExternalIds = Hotel::query()
                ->where('city_name', $recommendedHotelCityName)
                ->pluck('external_id')
                ->all();
        }

        $candidates = Customer::query()
            ->whereNotNull('email')
            ->orderByDesc('avg_score')
            ->orderByDesc('confirmed_reservations')
            ->limit(max($limit * 6, 200))
            ->get();

        $scored = $candidates->map(function (Customer $c) use (
            $recommendedHotelExternalId,
            $peerExternalIds,
            $focusAdr,
        ) {
            $score = 0.0;

            if ($recommendedHotelExternalId !== null && $c->hotel_external_id === $recommendedHotelExternalId) {
                $score += 100;
            } elseif (! empty($peerExternalIds) && in_array($c->hotel_external_id, $peerExternalIds, true)) {
                $score += 60;
            }

            if ($focusAdr !== null && $c->confirmed_reservations_adr !== null) {
                $delta = abs($c->confirmed_reservations_adr - $focusAdr);
                $band = $focusAdr * 0.3;
                if ($band > 0 && $delta <= $band) {
                    $score += 20 * (1 - $delta / $band);
                }
            }

            $score += min(10, (float) ($c->avg_score ?? 0));
            $score += min(20, (int) $c->confirmed_reservations);

            return [$c, $score];
        })->sortByDesc(fn ($pair) => $pair[1])->values();

        $blockedEmails = EmailUnsubscribe::query()->pluck('email')->all();
        $seenEmails = array_flip($blockedEmails);

        $picked = collect();
        foreach ($scored as [$customer, $_score]) {
            $email = (string) $customer->email;
            if (isset($seenEmails[$email])) {
                continue;
            }
            $seenEmails[$email] = true;
            $picked->push($customer);
            if ($picked->count() >= $limit) {
                break;
            }
        }

        return $picked;
    }
}
