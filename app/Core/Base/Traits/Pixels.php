<?php

namespace App\Core\Base\Traits;


trait Pixels
{

    protected $_sys_lang = array('es' => 'es-la', 'en' => 'en-us', 'it' => 'it-it', 'fr' => 'fr-fr', 'pt' => 'pt-la', 'de' => 'de-de', 'da' => 'da-da', 'ru' => 'ru-ru', 'ja' => 'ja-jp', 'nl' => 'nl-nl', 'ko' => 'ko-kr', 'lv' => 'lv-lv');


    public function retargeting($type, $product, $price_id, $price_line_id) {
        return null;
        // TODO deprecated code Andrea authorized change
//        if (in_array(request('client_site_id'), [1, 1000, 992, 1002, 1015, 999])) {
//            $mkt_product = $this->get_mkt_product_detail($type, $product, $price_id, $price_line_id);
//            return "rtgsettings ={ 'pdt_id': '" . $mkt_product['id'] . "', 'pdt_sku': '" . $mkt_product['id'] . "', 'pdt_name': '" . $mkt_product['name'] . "', 'pdt_price': '" . $mkt_product['price'] . "', 'pdt_amount': '" . $mkt_product['price'] . "', 'pdt_currency': '" . $mkt_product['currency'] . "', 'pdt_url': '" . config('app.url') . "/" . $mkt_product['url'] . "', 'pdt_photo': '" . config('app.url') . "/" . $mkt_product['img'] . "', 'pdt_instock': '1', 'pdt_expdate': '<!--" . $mkt_product['exp_date'] . "', 'pdt_category_list': '" . $mkt_product['category'] . "', 'pdt_smalldescription': '" . $mkt_product['description'] . "', 'pagetype': 'product', 'key': 'GTM', 'token': 'Trillonario_GBL', 'layer': 'iframe'};(function(d) {var s = d.createElement('script'); s.async = true;s.id='madv2014rtg'; s.type='text/javascript'; s.src = (d.location.protocol == 'https:' ? 'https:' : 'http:') + '//www.mainadv.com/Visibility/Rtggtm2-min.js'; var a = d.getElementsByTagName('script')[0];  a.parentNode.insertBefore(s, a);}(document));";
//        }
    }

    public function get_mkt_product_lottoelite_params($countryCode) {
        $params = '';
        switch ($countryCode) {
            case 'AU':
            case 'DE':
                $params = 'account=e7652e8b';
                break;
            case 'PL':
                $params = 'account=b6487be0';
                break;
            default:
                $params = 'account=29b6e3c2';
                break;
        }
        $params .= '&cpa=yes&utm_medium=retargeting&utm_source=netgroupmedia&utm_campaign=tri';
        return $params;
    }

    public function get_mkt_product_detail($type, $product, $price_id, $price_line_id) {

        $product_id = isset($product['id']) ? $product['id'] : null;
        $product_hc = $this->product_description($type, request('client_lang'), $product_id);
        $product_url = isset($product['url']) ? $product['url'] : null;
        $lang = request('client_lang');
        $mkt_product = array();
        $mkt_product_lottoelite_params = $this->get_mkt_product_lottoelite_params(request('client_country_iso'));

        $sys_id = request('client_sys_id');
        $curr_code = request('country_currency');

        switch ($type) {
            case '1' : // Lottery
                $_mkt_prod_id = $sys_id . '-1-' . $product_id . '-' . $price_id . '-' . $curr_code;
                $_mkt_prod_category = 'Lottery';
                $product_name = isset($product['name']) ? $product['name'] : null;
                $_mkt_prod_url = $product_url . '.php?' . $mkt_product_lottoelite_params;
                $_mkt_prod_img = 'images_v3/' . $lang . '/lottos/' . $product_id . '_big.png';
                break;
            case '2' : // Syndicate
                $_mkt_prod_id = $sys_id . '-2-' . $product_id . '-' . $price_id . '-' . $curr_code;
                $_mkt_prod_category = 'Syndicate';
                $product_name = isset($product_hc['name']) ? $product_hc['name'] : null;
                $_mkt_prod_url = 'play_syndicate.php?syndicate_id=' . $product_id . '&' . $mkt_product_lottoelite_params;
                $_mkt_prod_img = 'images_v3/' . $lang . '/lottos/' . $product_id . '_big_syndicate.png';
                break;
            case '3':  //  Raffles Syndicate
                $_mkt_prod_id = $sys_id . '-3-' . $product_id . '-' . $price_id . '-' . $curr_code;
                $_mkt_prod_category = 'Raffles Syndicate';
                $product_name = isset($product_hc['name']) ? $product_hc['name'] : null;
                $_mkt_prod_url = 'play_syndicate_raffle.php?rsyndicate_id=' . $product_id . '&' . $mkt_product_lottoelite_params;
                $_mkt_prod_img = 'images_v3/' . $lang . '/lottos/' . $product_id . '_big_syndicate.png';
                break;
            case '4':  //Raffles
                $_mkt_prod_id = $sys_id . '-4-' . $product_id . '-' . $curr_code;
                $_mkt_prod_category = 'Raffles';
                $product_name = isset($product_hc['name']) ? $product_hc['name'] : null;
                $_mkt_prod_url = 'play_raffle.php?rff_type=' . $product_id . '&' . $mkt_product_lottoelite_params;
                $_mkt_prod_img = 'images_v3/' . $lang . '/lottos/raffle_' . $product_id . '_big_raffle.png';
                break;
            case '7': //Scratches
                $_mkt_prod_id = $sys_id . '-7-' . $product_id . '-' . $price_id . '-' . $curr_code;
                $_mkt_prod_category = 'Scratch Card';
                $product_name = isset($product['name']) ? $product['name'] : null;
                $_mkt_prod_url = 'scratchcards-game-page.php?id=' . $product_id . '&' . $mkt_product_lottoelite_params;
                $_mkt_prod_img = 'images_v3/scratchcards/games-pics/' . $product_id . '_big.jpg';
                break;
        }

        $mkt_product['id'] = $_mkt_prod_id;
        $mkt_product['price'] = $price_line_id;
        $mkt_product['name'] = $product_name;
        $productHcDesc = null;
            if(is_array($product_hc)){
              $productHcDesc = array_key_exists('desc', $product_hc)
                    ? $product_hc[ 'desc' ] : null;
            }
        $mkt_product[ 'description' ] = $productHcDesc ;
        $mkt_product['category'] = $_mkt_prod_category;
        $mkt_product['exp_date'] = date("Y-m-d", strtotime('+30 day', strtotime(date("Y-m-d"))));
        $mkt_product['currency'] = $curr_code;
        $mkt_product['url'] = $_mkt_prod_url;
        $mkt_product['img'] = $_mkt_prod_img;

        return $mkt_product;
    }


    public function get_mkt_products_to_basket() {

        $_mkt_products = [
            'product_id' => '',
            'order_category' => '',
            'order_url' => '',
        ];

        $sys_id = request('client_sys_id');
        $curr_code = request('country_currency');
        $site_domain = config('app.url');
        $mkt_product_lottoelite_params = $this->get_mkt_product_lottoelite_params(request('client_country_iso'));
        $this->cart_subscriptions->each(function ($item) use ($sys_id, $curr_code, $site_domain, $mkt_product_lottoelite_params, &$_mkt_products) {
            $_mkt_products['product_id'] = '|' . $sys_id . '-1-' . $item->lot_id . '-' . $item->cts_prc_id . '-' . $curr_code;
            $_mkt_products['order_category'] = '|Lottery';
            $_mkt_products['order_url'] = '|' . $site_domain . '/play_lottery.php?lot_id=' . $item->lot_id . '&' . $mkt_product_lottoelite_params;

        });
        $this->syndicate_cart_subscriptions->each(function ($item) use ($sys_id, $curr_code, $site_domain, $mkt_product_lottoelite_params, &$_mkt_products) {
            $_mkt_products['product_id'] .= '|' . $sys_id . '-1-' . $item->syndicate_id . '-' . $item->cts_syndicate_prc_id . '-' . $curr_code;
            $_mkt_products['order_category'] .= '|Syndicate';
            $_mkt_products['order_url'] .= '|' . $site_domain . '/play_syndicate.php?syndicate_id=' . $item->syndicate_id . '&' . $mkt_product_lottoelite_params;
        });
        $this->syndicate_cart_raffles->each(function ($item) use ($sys_id, $curr_code, $site_domain, $mkt_product_lottoelite_params, &$_mkt_products) {
            $_mkt_products['product_id'] .= '|' . $sys_id . '-3-' . $item->rsyndicate_id . '-' . $item->cts_syndicate_prc_id . '-' . $curr_code;
            $_mkt_products['order_category'] .= '|Raffles Syndicate';
            $_mkt_products['order_url'] .= '|' . $site_domain . '/play_syndicate_raffle.php?rsyndicate_id=' . $item->rsyndicate_id . '&' . $mkt_product_lottoelite_params;
        });
        $this->cart_raffles->each(function ($item) use ($sys_id, $curr_code, $site_domain, $mkt_product_lottoelite_params, &$_mkt_products) {
            $_mkt_products['product_id'] .= '|' . $sys_id . '-4-' . $item->inf_id . '-' . $curr_code;
            $_mkt_products['order_category'] .= '|Raffles';
            $_mkt_products['order_url'] .= '|' . $site_domain . '/play_raffle.php?rff_type=' . $item->inf_id . '&' . $mkt_product_lottoelite_params;
        });
        $this->scratches_cart_subscriptions->each(function ($item) use ($sys_id, $curr_code, $site_domain, $mkt_product_lottoelite_params, &$_mkt_products) {
            $_mkt_products['product_id'] .= '|' . $sys_id . '-7-' . $item['scratches_id'] . '-' . $item['cts_prc_id'] . '-' . $curr_code;
            $_mkt_products['order_category'] .= '|Scratch Card';
            $_mkt_products['order_url'] .= '|' . $site_domain . '/scratchcards-game-page.php?id=' . $item['scratches_id'] . '&' . $mkt_product_lottoelite_params;
        });

        $_mkt_products['product_id'] = $_mkt_products['product_id'] != '' ? substr($_mkt_products['product_id'], 1) : '';
        $_mkt_products['order_category'] = $_mkt_products['order_category'] != '' ? substr($_mkt_products['order_category'], 1) : '';
        $_mkt_products['order_url'] = $_mkt_products['order_url'] != '' ? substr($_mkt_products['order_url'], 1) : '';
        $_mkt_products['order_date'] = $this->crt_date;
        return $_mkt_products;
    }

    public function cart_step1() {
        if (in_array(request('client_site_id'), [1, 1000, 992, 1002, 1015, 999])) {
            $subtotal = $this->crt_total;
            $_mkt_product = $this->get_mkt_products_to_basket();
            return "rtgsettings ={'pdt_id': '" . $_mkt_product['product_id'] . "', 'pdt_sku': '" . $_mkt_product['product_id'] . "','pdt_category_list': '" . $_mkt_product['order_category'] . "','pdt_url': '" . $_mkt_product['order_url'] . "','ty_orderamt':'" . $subtotal . "','ty_orderdate':'" . $_mkt_product['order_date'] . "','ty_orderstatus':'Pending','pagetype': 'basket','key': 'GTM','token': 'Trillonario_GBL','layer': 'iframe'};(function(d) {var s = d.createElement('script'); s.async = true;s.id='madv2014rtg';s.type='text/javascript';s.src = (d.location.protocol == 'https:' ? 'https:' : 'http:') + '//www.mainadv.com/Visibility/Rtggtm2-min.js';var a = d.getElementsByTagName('script')[0]; a.parentNode.insertBefore(s, a);}(document));";
        }
    }

    public function pixels_index() {
        if (in_array(request('client_site_id'), [1, 1000, 992, 1002, 1015, 999])) {
            $site_domain = config('app.url');
            $_mkt_product_params = $this->get_mkt_product_lottoelite_params(request('client_country_iso'));
            return "rtgsettings ={'pdt_url': '" . $site_domain . "?" . $_mkt_product_params . "','pagetype': 'home','key': 'GTM','token': 'Trillonario_GBL','layer': 'iframe'};(function(d) {var s = d.createElement('script'); s.async = true;s.id='madv2014rtg';s.type='text/javascript';s.src = (d.location.protocol == 'https:' ? 'https:' : 'http:') + '//www.mainadv.com/Visibility/Rtggtm2-min.js';var a = d.getElementsByTagName('script')[0]; a.parentNode.insertBefore(s, a);}(document));";
        }
    }

    protected function product_description($type, $lang, $id) {
        $product[1][2]['desc']['en'] = 'The lottery preferred in USA and all over the world';
        $product[1][3]['desc']['en'] = 'The lottery with the richest jackpot in history';
        $product[1][4]['desc']['en'] = 'Win Millions with the Golden State lotto';
        $product[1][5]['desc']['en'] = 'The big lotto from the Big Apple';
        $product[1][6]['desc']['en'] = 'Super shiny jackpots from the sunshine state';
        $product[1][8]['desc']['en'] = 'Europe�s richest weekly lotto';
        $product[1][9]['desc']['en'] = 'Fast, simple and fun to play';
        $product[1][11]['desc']['en'] = 'Fast, simple and fun to play';
        $product[1][12]['desc']['en'] = 'A lotto you can play all year round';
        $product[1][13]['desc']['en'] = 'Big weekly draws, 7 annual super-draws';
        $product[1][14]['desc']['en'] = 'The giant of the Land Down Under';
        $product[1][15]['desc']['en'] = 'The leading lottery on cash prizes';
        $product[1][16]['desc']['en'] = 'The big money lotto from Blighty';
        $product[1][17]['desc']['en'] = 'The rapid rollover lotto with a Gallic twist';
        $product[1][18]['desc']['en'] = 'The world�s craziest rapid rollover lotto';
        $product[1][19]['desc']['en'] = 'Spain�s most popular weekly lotto';
        $product[1][20]['desc']['en'] = 'The short odds, rapid play game from the USA';
        $product[1][21]['desc']['en'] = 'The world�s luckiest lotto';
        $product[1][22]['desc']['en'] = 'Enhanced winning odds on the fun state lotto';
        $product[1][23]['desc']['en'] = 'Pay a little, play a lot, win big';
        $product[1][24]['desc']['en'] = 'The world�s biggest international lotto';
        $product[1][25]['desc']['en'] = '';
        $product[1][32]['desc']['en'] = '';
        $product[1][34]['desc']['en'] = '';
        $product[1][39]['desc']['en'] = '';

        $product[1][2]['desc']['es'] = 'La preferida de Estados Unidos� y del mundo';
        $product[1][3]['desc']['es'] = 'La loter�a due�a del mayor premio de la historia';
        $product[1][4]['desc']['es'] = 'Una loter�a estatal a la altura de las grandes';
        $product[1][5]['desc']['es'] = 'Una veterana con grandes premios acumulados';
        $product[1][6]['desc']['es'] = 'Buenas probabilidades, grandes premios en efectivo';
        $product[1][8]['desc']['es'] = 'Impresionantes 1 en 13 chances de ganar';
        $product[1][9]['desc']['es'] = 'La loter�a r�pida, sencilla y divertida de jugar';
        $product[1][11]['desc']['es'] = 'La mayor y m�s importante loter�a de Latinoam�rica';
        $product[1][12]['desc']['es'] = 'El Gordo en el que podr�s jugar todo el a�o';
        $product[1][13]['desc']['es'] = 'Grandes sorteos semanales, 7 s�per sorteos anuales';
        $product[1][14]['desc']['es'] = 'La gigante millonaria del hemisferio sur ';
        $product[1][15]['desc']['es'] = 'La loter�a l�der en pagos de premios en efectivo';
        $product[1][16]['desc']['es'] = 'La loter�a m�s grande del Reino Unido';
        $product[1][17]['desc']['es'] = 'La loter�a de los premios en constante crecimiento';
        $product[1][18]['desc']['es'] = 'La italiana que conquist� a toda Europa';
        $product[1][19]['desc']['es'] = 'La loter�a bisemanal m�s querida de toda Espa�a';
        $product[1][20]['desc']['es'] = 'Nueve maneras distintas de ganar un premio';
        $product[1][21]['desc']['es'] = 'Las mejores chances de ganar del mercado';
        $product[1][22]['desc']['es'] = 'Menos n�meros que elegir, m�s chances de ganar';
        $product[1][23]['desc']['es'] = 'Apuesta poco, juega seguido y gana en grande';
        $product[1][24]['desc']['es'] = 'La novata que se convirti� en �xito instant�neo';
        $product[1][25]['desc']['es'] = 'Doble chance de hacerse millonario con un ticket';
        $product[1][32]['desc']['es'] = 'Two chances to become a millionaire';
        $product[1][34]['desc']['es'] = '';
        $product[1][39]['desc']['es'] = '';

        $product[1][2]['desc']['pt'] = 'A preferida dos Estados Unidos� e do mundo';
        $product[1][3]['desc']['pt'] = 'A loteria dona do maior pr�mio da historia';
        $product[1][4]['desc']['pt'] = 'Uma loteria estadual no n�vel das grandes';
        $product[1][5]['desc']['pt'] = 'Uma veterana com grandes pr�mios  acumulados';
        $product[1][6]['desc']['pt'] = 'Boas probabilidades, grandes pr�mios  em dinheiro';
        $product[1][8]['desc']['pt'] = 'Impressionantes 1 em 13 chances de ganhar';
        $product[1][9]['desc']['pt'] = 'A loteria r�pida, simples e divertida de jogar';
        $product[1][11]['desc']['pt'] = 'A mais importante loteria da Am�rica Latina';
        $product[1][12]['desc']['pt'] = 'El Gordo onde voc� poder� jogar o ano inteiro';
        $product[1][13]['desc']['pt'] = 'Grandes sorteios semanais, 7 super sorteios anuais';
        $product[1][14]['desc']['pt'] = 'A gigante milion�ria do hemisf�rio sul';
        $product[1][15]['desc']['pt'] = 'Grandes sorteios semanais, 7 super sorteios anuais';
        $product[1][16]['desc']['pt'] = 'A maior loteria do Reino Unido';
        $product[1][17]['desc']['pt'] = 'A loteria dos pr�mios em constante crescimento';
        $product[1][18]['desc']['pt'] = 'A italiana que conquistou a Europa toda';
        $product[1][19]['desc']['pt'] = 'A loteria bissemanal mais querida da Espanha';
        $product[1][20]['desc']['pt'] = 'Nove formas diferentes de ganhar um pr�mio';
        $product[1][21]['desc']['pt'] = 'As melhores chances de ganhar do mercado';
        $product[1][22]['desc']['pt'] = 'Menos n�meros pra escolher, mais chances de ganhar';
        $product[1][23]['desc']['pt'] = 'Aposte pouco, jogue muito e ganhe pra valer';
        $product[1][24]['desc']['pt'] = 'A novata que se tornou um sucesso instant�neo';
        $product[1][25]['desc']['pt'] = 'Dupla chance de virar milion�rio com um bilhete';
        $product[1][32]['desc']['pt'] = 'Um milion�rio para desfrutar durante toda a sua vida';
        $product[1][34]['desc']['pt'] = 'Quina, acerte as 5 dezenas e fique rico!';
        $product[1][39]['desc']['pt'] = 'Lotof�cil, f�cil de apostar, f�cil de ganhar!';

//Falta nombre en ingles de las Rifas
        $product[4][1]['name']['en'] = 'Saturday Draw - Spanish Millionaire Raffle';
        $product[4][2]['name']['en'] = 'Thursday Draw - Spanish Millionaire Raffle';
        $product[4][21]['name']['en'] = 'Sorteo Especial - Mexican Millionaire Raffles';
        $product[4][17]['name']['en'] = 'Sorteo Mayor - Mexican Millionaire Raffles';
        $product[4][18]['name']['en'] = 'Sorteo Superior - Mexican Millionaire Raffles';
        $product[4][27]['name']['en'] = 'Sorteo de Diez - Mexican Millionaire Raffles';
        $product[4][20]['name']['en'] = 'Sorteo Zod�aco - Mexican Millionaire Raffles';
        $product[4][26]['name']['en'] = 'Sorteo Zod�aco - Mexican Millionaire Raffles';
        $product[4][3]['name']['en'] = 'Special Draw - Spanish Millionaire Raffle';
        $product[4][4]['name']['en'] = 'Super Summer Draw - Spanish Millionaire Raffle';

        $product[4][1]['name']['es'] = 'Sorteo del s�bado - Loter�a Nacional de Espa�a';
        $product[4][2]['name']['es'] = 'Sorteo del jueves - Loter�a Nacional de Espa�a';
        $product[4][21]['name']['es'] = 'Sorteo Especial - Loter�a Nacional de M�xico';
        $product[4][17]['name']['es'] = 'Sorteo Mayor - Loter�a Nacional de M�xico';
        $product[4][18]['name']['es'] = 'Sorteo Superior - Loter�a Nacional de M�xico';
        $product[4][27]['name']['es'] = 'Sorteo de Diez - Loter�a Nacional de M�xico';
        $product[4][20]['name']['es'] = 'Sorteo Zod�aco - Loter�a Nacional de M�xico';
        $product[4][26]['name']['es'] = 'Sorteo Zod�aco - Loter�a Nacional de M�xico';
        $product[4][3]['name']['es'] = 'Sorteo Especial - Loteria Nacional de Espa�a';
        $product[4][4]['name']['en'] = 'Sorteo Extraordinario de Vacaciones - Loteria Nacional de Espa�a';

        $product[4][1]['name']['pt'] = 'Sorteio do S�bado - Loteria Nacional da Espanha';
        $product[4][2]['name']['pt'] = 'Sorteio da Quinta-feira - Loteria Nacional da Espanha';
        $product[4][21]['name']['pt'] = 'Sorteio Especial - Loteria Nacional do M�xico';
        $product[4][17]['name']['pt'] = 'Sorteio Mayor - Loteria Nacional do M�xico';
        $product[4][18]['name']['pt'] = 'Sorteio Superior - Loteria Nacional do M�xico';
        $product[4][27]['name']['pt'] = 'Sorteio de Diez - Loteria Nacional do M�xico';
        $product[4][20]['name']['pt'] = 'Sorteio Zod�aco  - Loteria Nacional do M�xico';
        $product[4][26]['name']['pt'] = 'Sorteio Zod�aco  - Loteria Nacional do M�xico';
        $product[4][3]['name']['pt'] = 'Sorteio especial - Loteria Nacional da Espanha';
        $product[4][4]['name']['pt'] = 'Super Sorteio de Ver�o - Loteria Nacional da Espanha';

        $product[4][1]['desc']['es'] = 'Mejores probabilidades que cualquier otra loter�a';
        $product[4][2]['desc']['es'] = 'El sorteo de la loter�a m�s antigua del mundo';
        $product[4][21]['desc']['es'] = '2� mayor sorteo de M�xico, con 2 premios mensuales';
        $product[4][17]['desc']['es'] = 'Uno de los juegos m�s populares de todo M�xico';
        $product[4][18]['desc']['es'] = 'Excelentes premios, grandes probabilidades';
        $product[4][27]['desc']['es'] = 'Incre�bles chances de ganar un premio en efectivo';
        $product[4][20]['desc']['es'] = 'Un juego �nico en el mundo de las loter�as';
        $product[4][26]['desc']['es'] = 'Un sorteo especial, con un premio mucho m�s grande';

        $product[4][1]['desc']['en'] = 'The best winnings odds of any national lotery';
        $product[4][2]['desc']['en'] = 'The world�s oldest lottery game';
        $product[4][21]['desc']['en'] = '2nd largest draw in Mexico, with 2 monthly prizes';
        $product[4][17]['desc']['en'] = 'One of the most popular lottery games in Mexico';
        $product[4][18]['desc']['en'] = 'Great odds, even better prizes';
        $product[4][27]['desc']['en'] = 'Incredible chances to win big cash prizes';
        $product[4][20]['desc']['en'] = 'A truly unique lottery game';
        $product[4][26]['desc']['en'] = 'A special draw with a bigger jackpot';

        $product[4][1]['desc']['pt'] = 'Melhores probabilidades que qualquer outra loteria';
        $product[4][2]['desc']['pt'] = 'O sorteio da loteria mais antiga do mundo';
        $product[4][21]['desc']['pt'] = '2� maior sorteio do M�xico, com 2 pr�mios mensais';
        $product[4][17]['desc']['pt'] = 'Um dos jogos mais populares de todo M�xico';
        $product[4][18]['desc']['pt'] = 'Excelentes pr�mios, grandes probabilidades';
        $product[4][27]['desc']['pt'] = 'Incr�veis chances de ganhar um pr�mio em dinheiro';
        $product[4][20]['desc']['pt'] = 'Um jogo �nico no mundo das loterias';
        $product[4][26]['desc']['pt'] = 'Um sorteio especial, com um pr�mio muito maior';

        $product[2][113]['name']['en'] = 'Mega Millions';
        $product[2][120]['name']['en'] = 'Powerball';
        $product[2][111]['name']['en'] = 'SuperEnalotto';
        $product[2][106]['name']['en'] = 'Power Combo';
        $product[2][101]['name']['en'] = 'Euro Millions Max';
        $product[2][110]['name']['en'] = 'Euro Millions 50';
        $product[2][116]['name']['en'] = 'EuroJackpot';
        $product[2][117]['name']['en'] = 'Euro Combo';
        $product[2][118]['name']['en'] = 'Euro Club';
        $product[2][112]['name']['en'] = 'Irish Lotto';
        $product[2][102]['name']['en'] = 'Florida Lotto';
        $product[2][115]['name']['en'] = 'Oz Lotto';
        $product[2][114]['name']['en'] = 'La Primitiva';
        $product[2][143]['name']['en'] = 'World Combo';
        $product[2][148]['name']['en'] = 'Monster Combo';
        $product[2][145]['name']['en'] = 'Lucky 7 Combo';

        $product[2][113]['name']['es'] = 'Mega Millions';
        $product[2][120]['name']['es'] = 'Powerball';
        $product[2][111]['name']['es'] = 'SuperEnalotto';
        $product[2][106]['name']['es'] = 'Power Combo';
        $product[2][101]['name']['es'] = 'Euro Millions Max';
        $product[2][110]['name']['es'] = 'Euro Millions 50';
        $product[2][116]['name']['es'] = 'EuroJackpot';
        $product[2][117]['name']['es'] = 'Euro Combo';
        $product[2][118]['name']['es'] = 'Euro Club';
        $product[2][112]['name']['es'] = 'Irish Lotto';
        $product[2][102]['name']['es'] = 'Florida Lotto';
        $product[2][115]['name']['es'] = 'Oz Lotto';
        $product[2][114]['name']['es'] = 'La Primitiva';

        $product[2][113]['name']['pt'] = 'Mega Millions';
        $product[2][120]['name']['pt'] = 'Powerball';
        $product[2][111]['name']['pt'] = 'SuperEnalotto';
        $product[2][106]['name']['pt'] = 'Power Combo';
        $product[2][101]['name']['pt'] = 'Euro Millions Max';
        $product[2][110]['name']['pt'] = 'Euro Millions 50';
        $product[2][116]['name']['pt'] = 'EuroJackpot';
        $product[2][117]['name']['pt'] = 'Euro Combo';
        $product[2][118]['name']['pt'] = 'Euro Club';
        $product[2][112]['name']['pt'] = 'Irish Lotto';
        $product[2][102]['name']['pt'] = 'Florida Lotto';
        $product[2][115]['name']['pt'] = 'Oz Lotto';
        $product[2][114]['name']['pt'] = 'La Primitiva';

        $product[2][113]['desc']['es'] = 'La estrategia perfecta para ganar sus millones';
        $product[2][120]['desc']['es'] = '70 chances de ganar miles de millones cada semana';
        $product[2][111]['desc']['es'] = 'Frecuentes premios acumulados, 3 sorteos semanales';
        $product[2][106]['desc']['es'] = 'Las 3 loter�as m�s millonarias en un solo lugar';
        $product[2][101]['desc']['es'] = 'Impresionantes 50 chances de ganar en cada sorteo';
        $product[2][110]['desc']['es'] = 'La forma m�s inteligente de jugar en Euro Millions';
        $product[2][116]['desc']['es'] = 'A�n m�s chances de alcanzar su premio millonario';
        $product[2][117]['desc']['es'] = 'Euro Millions y EuroJackpot suman sus millones';
        $product[2][118]['desc']['es'] = 'El camino r�pido hacia el premio de Euro Millions';
        $product[2][112]['desc']['es'] = 'La opci�n con 42% m�s chances de ganar que otras';
        $product[2][102]['desc']['es'] = 'Una forma f�cil de aumentar sus chances de ganar';
        $product[2][115]['desc']['es'] = 'M�s chances por menos para llegar a sus millones';
        $product[2][114]['desc']['es'] = 'El lugar ideal para maximizar sus chances de ganar';

        $product[2][113]['desc']['en'] = 'The perfect game to help you win Millions';
        $product[2][120]['desc']['en'] = '70 chances to win Billions every week';
        $product[2][111]['desc']['en'] = '3 weekly draws and regular rollovers';
        $product[2][106]['desc']['en'] = 'The 3 richest lotteries in one game';
        $product[2][101]['desc']['en'] = '50 chances to win in every draw';
        $product[2][110]['desc']['en'] = 'The smartest way to play EuroMillions';
        $product[2][116]['desc']['en'] = 'Even more chances to win a Millionaire prize';
        $product[2][117]['desc']['en'] = 'EuroMillions and Eurojackpot in one great game';
        $product[2][118]['desc']['en'] = 'The fast road to the EuroMillions� jackpot';
        $product[2][112]['desc']['en'] = '4.5 times easier to win than UK Lotto';
        $product[2][102]['desc']['en'] = 'A great game to boost you jackpot chances';
        $product[2][115]['desc']['en'] = 'Spend less and get more chances to win Millions';
        $product[2][114]['desc']['en'] = 'The ideal game to enhance your winning odds';

        $product[2][113]['desc']['pt'] = 'A estrat�gia perfeita para ganhar seus milh�es';
        $product[2][120]['desc']['pt'] = '70 chances de ganhar muitos milh�es a cada semana';
        $product[2][111]['desc']['pt'] = 'Frequentes pr�mios acumulados, 3 sorteios semanais';
        $product[2][106]['desc']['pt'] = 'A 3 loterias m�s milion�rias em um s� lugar';
        $product[2][101]['desc']['pt'] = 'Impressionantes 50 chances de ganhar em cada sorteio';
        $product[2][110]['desc']['pt'] = 'A forma mais inteligente de jogar na Euro Millions';
        $product[2][116]['desc']['pt'] = 'Ainda mais chances de ganhar seu pr�mio milion�rio';
        $product[2][117]['desc']['pt'] = 'Euro Millions e EuroJackpot somam seus milh�es';
        $product[2][118]['desc']['pt'] = 'O caminho r�pido at� o pr�mio de Euro Millions';
        $product[2][112]['desc']['pt'] = 'A op��o com 42% mais chances de ganhar que outras';
        $product[2][102]['desc']['pt'] = 'Uma forma f�cil de aumentar suas chances de ganhar';
        $product[2][115]['desc']['pt'] = 'Mais chances por menos para chegar a seus milh�es';
        $product[2][114]['desc']['pt'] = 'Perfeita para maximizar suas chances de ganhar';


        $product[3][204]['name']['en'] = 'Club Mayor';
        $product[3][203]['name']['en'] = 'Club Superior';
        $product[3][206]['name']['en'] = 'Club de Diez';
        $product[3][207]['name']['en'] = 'Super Summer Draw';

        $product[3][204]['name']['es'] = 'Club Mayor';
        $product[3][203]['name']['es'] = 'Club Superior';
        $product[3][206]['name']['es'] = 'Club de Diez';
        $product[3][207]['name']['es'] = 'Extraordinario de Vacaciones';

        $product[3][204]['name']['pt'] = 'Club Mayor';
        $product[3][203]['name']['pt'] = 'Club Superior';
        $product[3][206]['name']['pt'] = 'Club de Diez';
        $product[3][207]['name']['pt'] = 'Super Sorteio de Ver�o';

        $product[3][204]['desc']['en'] = 'Every ticket gives you 1-in-7 chances to win';
        $product[3][203]['desc']['en'] = 'More chances to win Mexico�s Sorteo Superior';
        $product[3][206]['desc']['en'] = 'The perfect game to make you a Millionaire';

        $product[3][204]['desc']['es'] = 'Con cada ticket, 1 en 7 chances de ganar un premio';
        $product[3][203]['desc']['es'] = 'M�s cerca de ganar el Sorteo Superior de M�xico';
        $product[3][206]['desc']['es'] = 'Su oportunidad de perfecta para hacerse millonario';

        $product[3][204]['desc']['pt'] = 'A cada bilhete, 1 em 7 chances de ganhar um premio';
        $product[3][203]['desc']['pt'] = 'Mais perto de ganhar o Sorteio Superior do M�xico';
        $product[3][206]['desc']['pt'] = 'Sua oportunidade perfeita para virar milion�rio';

        $product[7]['desc']['pt'] = 'Raspadinhas, ganhe pr�mio enormes na hora!';

        $product1 = null;
        if (isset($product[$type][$id]['desc'])) {
            $product1['desc'] = isset($product[$type][$id]['desc'][$lang]) ? $product[$type][$id]['desc'][$lang] : $product[$type][$id]['desc']['en'];
        }
        if (isset($product[$type][$id]['name'])) {
            $product1['name'] = isset($product[$type][$id]['name'][$lang]) ? $product[$type][$id]['name'][$lang] : $product[$type][$id]['name']['en'];
        }
        return $product1;

    }
}
