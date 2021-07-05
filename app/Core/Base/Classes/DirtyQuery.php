<?php

namespace App\Core\Base\Classes;

class DirtyQuery
{

    public static function getQueryProductsByCart($crt_id)
    {
        return "SELECT cts_id as id, 'Lottery' as type, name_fancy_en as name, desc_en as description, cts_price as amount, if (cart_suscriptions.bonus_id != 0, code, '') as bonus_code FROM cart_suscriptions INNER JOIN lotteries ON lotteries.lot_id = cart_suscriptions.lot_id INNER JOIN lotteries_extra_info ON lotteries_extra_info.lot_id = lotteries.lot_id INNER JOIN carts ON carts.crt_id = cart_suscriptions.crt_id INNER JOIN promotions ON cart_suscriptions.bonus_id = promotions.bonus_id WHERE  carts.crt_id = $crt_id GROUP BY cts_id UNION SELECT cts_id as id, 'Syndicate' as type, syndicate.name as name, '' as description, cts_price as amount, if (syndicate_cart_subscriptions.bonus_id != 0, code, '')  as bonus_code FROM syndicate_cart_subscriptions, syndicate_prices, syndicate_lotto, syndicate, lotteries, promotions WHERE  syndicate_cart_subscriptions.crt_id = $crt_id AND  syndicate_cart_subscriptions.cts_syndicate_prc_id = syndicate_prices.prc_id AND syndicate_cart_subscriptions.syndicate_id = syndicate_prices.syndicate_id AND syndicate_cart_subscriptions.syndicate_id = syndicate_lotto.syndicate_id AND syndicate_cart_subscriptions.syndicate_id =  syndicate.id AND syndicate_lotto.lot_id = lotteries.lot_id AND syndicate_cart_subscriptions.bonus_id = promotions.bonus_id group by cts_id UNION SELECT cts_id as id, 'Membership' as type, memberships.name as name, description as description, memberships_cart_subscriptions.cts_price as amount, if (memberships_cart_subscriptions.bonus_id != 0, code, '')  as bonus_code FROM memberships_cart_subscriptions INNER JOIN  memberships ON memberships_cart_subscriptions.memberships_id = memberships.id INNER JOIN promotions ON memberships_cart_subscriptions.bonus_id = promotions.bonus_id WHERE  memberships_cart_subscriptions.crt_id = $crt_id UNION SELECT cart_raffles.crf_id as id, 'Raffle' as type, rff_name as name, rff_desc as description, crf_price as amount, if (cart_raffles.bonus_id != 0, code, '')  as bonus_code FROM cart_raffles INNER JOIN carts ON cart_raffles.crt_id = carts.crt_id INNER JOIN raffles ON cart_raffles.rff_id = raffles.rff_id INNER JOIN raffle_info ON raffles.inf_id = raffle_info.inf_id INNER JOIN promotions ON cart_raffles.bonus_id = promotions.bonus_id LEFT JOIN prices_raffles ON cart_raffles.crf_prc_rff_id = prices_raffles.prc_rff_id WHERE carts.crt_id = $crt_id GROUP BY cart_raffles.crf_id UNION SELECT cts_id as id, 'Raffle Syndicate' as type, syndicate_raffle.name as name, '' as description, cts_price as amount, if (syndicate_cart_raffles.bonus_id != 0, code, '')  as bonus_code FROM syndicate_cart_raffles INNER JOIN syndicate_raffle_prices ON syndicate_cart_raffles.cts_syndicate_prc_id = syndicate_raffle_prices.prc_id AND  syndicate_cart_raffles.rsyndicate_id = syndicate_raffle_prices.rsyndicate_id INNER JOIN syndicate_raffle_raffles ON syndicate_cart_raffles.rsyndicate_id = syndicate_raffle_raffles.rsyndicate_id INNER JOIN syndicate_raffle ON syndicate_raffle_raffles.rsyndicate_id = syndicate_raffle.id INNER JOIN promotions ON syndicate_cart_raffles.bonus_id = promotions.bonus_id WHERE  syndicate_cart_raffles.crt_id = $crt_id GROUP BY cts_id UNION SELECT cts_id as id, 'Scratch Card' as type, scratches.name as name, '' as description, cts_price as amount, if (scratches_cart_subscriptions.bonus_id != 0, code, '')  as bonus_code FROM scratches_cart_subscriptions INNER JOIN scratches_prices ON scratches_cart_subscriptions.cts_prc_id = scratches_prices.prc_id INNER JOIN scratches ON   scratches.id = scratches_cart_subscriptions.scratches_id INNER JOIN promotions ON scratches_cart_subscriptions.bonus_id = promotions.bonus_id WHERE scratches_cart_subscriptions.crt_id = $crt_id";
    }

    public static function getQueryUserRegisterLog($user_id)
    {
        return "SELECT CONCAT(usr_address1, ' ', usr_address2) AS address, LEFT(u.usr_cookies, 8) AS affiliate_reference, IF(usr_notTelemCall, FALSE, TRUE) AS allows_call_marketing, IF(oe.deleted, FALSE, TRUE) AS allows_email_marketing, IF(op.deleted, FALSE, TRUE) AS allows_notification_marketing, IF(odm.deleted, FALSE, TRUE) AS allows_post_marketing, IF(os.deleted, FALSE, TRUE) AS allows_sms_marketing, usr_birthdate AS birth_date, usr_city AS city, country_Iso AS country, curr_code AS currency, usr_email AS email, usr_name AS first_name, usr_lastname AS last_name, usr_ip AS ip_address, usr_mobile AS mobile, '' AS mobile_prefix, usr_notes AS note, usr_zipcode AS postal_code, usr_language AS language, IF(ut.gender IS NOT NULL, IF(ut.gender=0, 'Female', 'Male'),'NotKnown') AS sex, IF(u.usr_title > 0, ut.code, '') AS title, '' AS url_referer, '' AS user_agent, u.usr_id AS user_id, u.usr_email AS username, IF(usr_verification=0, 'Unknown', IF(usr_verification=1,'Unknown', IF(usr_verification=2, 'Unknown', 'Email'))) as verification_type, '' AS verified_at, s.site_url AS origin FROM users u INNER JOIN sites s ON s.site_id=u.site_id INNER JOIN countries c ON c.country_id=u.country_id LEFT JOIN optin_email oe  ON oe.usr_id=u.usr_id LEFT JOIN optin_push op ON op.usr_id=u.usr_id LEFT JOIN optin_direct_mail odm ON odm.usr_id=u.usr_id LEFT JOIN optin_sms os ON os.usr_id=u.usr_id LEFT JOIN users_title ut ON ut.id=u.usr_title WHERE u.usr_id=" . $user_id;
    }

    public static function getQueryCartIsFromTelem($crt_id)
    {

        return "SELECT ca.crt_id from carts ca left join telem_calls tc on tc.crt_id=ca.crt_id where ca.crt_id = ".$crt_id." and (ca.crt_host = 'admin telem' or tc.crt_id is not null) limit 1";
    }

    public static function getQueryCartLotterySubscription($cart): string
    {
        $crt_id = $cart->crt_id;
        return "SELECT SUM(cts_price) AS total FROM cart_suscriptions WHERE crt_id =  $crt_id";

    }

    public static function getQueryCartSyndicateSubscription($cart): string
    {
        $crt_id = $cart->crt_id;
        return "SELECT SUM(cts_price) AS total FROM syndicate_cart_subscriptions WHERE crt_id =  $crt_id";

    }

    public static function getQueryCartMemberShipsSubscription($cart): string
    {
        $crt_id = $cart->crt_id;
        return "SELECT SUM(cts_price) AS total FROM memberships_cart_subscriptions WHERE crt_id =  $crt_id";

    }

    public static function getQueryCartRafflesSubscription($cart): string
    {
        $crt_id = $cart->crt_id;
        return "SELECT SUM(crf_price) as total FROM cart_raffles WHERE crt_id =  $crt_id";

    }

    public static function getQueryCartSyndicateRafflesSubscription($cart): string
    {
        $crt_id = $cart->crt_id;
       return "SELECT SUM(cts_price) as total FROM syndicate_cart_raffles WHERE crt_id =  $crt_id";

    }


    public static function getQueryCartScratchesSubscription($cart): string
    {
        $crt_id = $cart->crt_id;
        return "SELECT SUM(cts_price) AS total FROM scratches_cart_subscriptions WHERE crt_id =  $crt_id";

    }

}
