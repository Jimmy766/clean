<?php /** @noinspection PhpConstantNamingConventionInspection */

/** @noinspection PhpMissingDocCommentInspection */

namespace App\Core\Base\Classes;

class ModelConst
{

    public const DISABLED = 0;
    public const ENABLED  = 1;

    public const PROGRAM_RANGE_UNDEFINED    = 0;
    public const PROGRAM_RANGE_DEFINED_DATE = 1;
    public const PROGRAM_RANGE_CURRENT_DATE = 2;

    public const PROGRAM_TYPE_CURRENT_WEEK = 0;
    public const PROGRAM_TYPE_CURRENT_YEAR = 1;

    public const PROGRAM_PERIOD_DATE = 0;
    public const PROGRAM_PERIOD_DAY  = 1;

    public const PROGRAM_TYPE_RANGE           = [
        [
            'id'   => ModelConst::PROGRAM_RANGE_UNDEFINED,
            'name' => 'Indefinite',
        ],
        [
            'id'   => ModelConst::PROGRAM_RANGE_DEFINED_DATE,
            'name' => 'Defined',
        ],
        [
            'id'   => ModelConst::PROGRAM_RANGE_CURRENT_DATE,
            'name' => 'Recurrent',
        ],
    ];
    public const PROGRAM_TYPE_RECURRENT_RANGE = [
        [
            'id'   => ModelConst::PROGRAM_TYPE_CURRENT_WEEK,
            'name' => 'week',
        ],
        [
            'id'   => ModelConst::PROGRAM_TYPE_CURRENT_YEAR,
            'name' => 'year',
        ],
    ];

    public const PROGRAM_PERIOD_RECURRENT = [
        [
            'id'   => ModelConst::PROGRAM_PERIOD_DATE,
            'name' => 'Date',
        ],
        [
            'id'   => ModelConst::PROGRAM_PERIOD_DAY,
            'name' => 'Day',
        ],
    ];

	public const TRANSLATION_STATUS_EDITING = 0;
	public const TRANSLATION_STATUS_TRANSLATED = 1;
	public const TRANSLATION_STATUS_APPROVED = 2;
	public const TRANSLATION_STATUS_DENIED = 3;

	public const TRANSLATION_STATUS_RANGE=[
		[
			'id'=>ModelConst::TRANSLATION_STATUS_EDITING,
			'name'=>'Editing'
		],
		[
			'id'=>ModelConst::TRANSLATION_STATUS_TRANSLATED,
			'name'=>'Translated'
		],
		[
			'id'=>ModelConst::TRANSLATION_STATUS_APPROVED,
			'name'=>'Approved'
		],
		[
			'id'=>ModelConst::TRANSLATION_STATUS_DENIED,
			'name'=>'Denied'
		],
	];
	public const MESSAGE_BATCH_STATUS_SCHEDULED = 0;
	public const MESSAGE_BATCH_STATUS_SENT = 1;
	public const MESSAGE_BATCH_STATUS_CANCELED = 2;

	public const MESSAGE_BATCH_STATUS_RANGE=[
		[
			'id'=>ModelConst::MESSAGE_BATCH_STATUS_SCHEDULED,
			'name'=>'Scheduled'
		],
		[
			'id'=>ModelConst::MESSAGE_BATCH_STATUS_SENT,
			'name'=>'Sent'
		],
		[
			'id'=>ModelConst::MESSAGE_BATCH_STATUS_CANCELED,
			'name'=>'Canceled'
		],
	];

    public const BLOCKEABLE_REGION   = 0;
    public const BLOCKEABLE_LANGUAGE = 1;

    public const BLOCKEABLE_LIST = [
        [
            'id'   => ModelConst::BLOCKEABLE_REGION,
            'name' => 'Region',
        ],
        [
            'id'   => ModelConst::BLOCKEABLE_LANGUAGE,
            'name' => 'Language',
        ],
    ];

    public const ENTITYABLE_LOTTERY = 0;
    public const ENTITYABLE_GAME    = 1;

    public const ENTITYABLE_LIST = [
        [
            'id'   => ModelConst::ENTITYABLE_LOTTERY,
            'name' => 'Lottery',
        ],
        [
            'id'   => ModelConst::ENTITYABLE_GAME,
            'name' => 'Game',
        ],
    ];

    public const TYPE_BLOCK_IP           = 0;
    public const TYPE_BLOCK_REGION       = 1;
    public const TYPE_BLOCK_LANGUAGE     = 2;
    public const TYPE_BLOCK_AFFILIATE    = 3;
    public const TYPE_BLOCK_ONE_PRODUCT  = 4;
    public const TYPE_BLOCK_TYPE_PRODUCT = 5;

    public const TYPE_BLOCK_LIST = [
        [
            'id'   => ModelConst::TYPE_BLOCK_IP,
            'name' => 'IP',
        ],
        [
            'id'   => ModelConst::TYPE_BLOCK_REGION,
            'name' => 'Region',
        ],
        [
            'id'   => ModelConst::TYPE_BLOCK_LANGUAGE,
            'name' => 'Language',
        ],
        [
            'id'   => ModelConst::TYPE_BLOCK_AFFILIATE,
            'name' => 'Affiliate',
        ],
        [
            'id'   => ModelConst::TYPE_BLOCK_ONE_PRODUCT,
            'name' => 'One Product',
        ],
        [
            'id'   => ModelConst::TYPE_BLOCK_TYPE_PRODUCT,
            'name' => 'Type Product',
        ],
    ];

    public const TYPE_BLOCK_TYPE_PRODUCT_LOTTERY   = 0;
    public const TYPE_BLOCK_TYPE_PRODUCT_GAME      = 1;

    public const TYPE_BLOCK_TYPE_PRODUCT_LIST = [
        [
            'id'   => ModelConst::TYPE_BLOCK_TYPE_PRODUCT_LOTTERY,
            'name' => 'Lottery',
        ],
        [
            'id'   => ModelConst::TYPE_BLOCK_TYPE_PRODUCT_GAME,
            'name' => 'Game',
        ],
    ];

    public const TYPE_EXCEPTION_IP     = 0;
    public const TYPE_EXCEPTION_DOMAIN = 1;

    public const TYPE_EXCEPTION_LIST = [
        [
            'id'   => ModelConst::TYPE_EXCEPTION_IP,
            'name' => 'IP',
        ],
        [
            'id'   => ModelConst::TYPE_EXCEPTION_DOMAIN,
            'name' => 'Domain',
        ],
    ];


    public const ROUTE_EXCEPTION = 'blocks/check';

    public const CHECK_INFORMATION_PROVIDE_ID_INSURE_BOOSTED_JACKPOT = 52;

    public const LOTTERY_MULTIPLER_MILLION = 1000000;

    public const LOTTERY_SUM_PREVIOUS_MILLION_JACKPOT = 12000000;

    public const LOTTERY_INSURE_MODIFIER = 1;


    public const LIST_CURRENCIES_USER_CODE = [
        [
            'id'   => 0,
            'name' => 'USD',
            'code' => 'USD',
        ],
        [
            'id'   => 1,
            'name' => 'EUR',
            'code' => 'EUR',
        ],
        [
            'id'   => 2,
            'name' => 'AUD',
            'code' => 'AUD',
        ],
        [
            'id'   => 3,
            'name' => 'GBP',
            'code' => 'GBP',
        ],
        [
            'id'   => 4,
            'name' => 'CAD',
            'code' => 'CAD',
        ],
        [
            'id'   => 5,
            'name' => 'BRL',
            'code' => 'BRL',
        ],
    ];


    public const TRILLONARIO_LANGUAGE_SPANISH = 'es-la';
    public const TRILLONARIO_LANGUAGE_ENGLISH = 'en-us';
    public const TRILLONARIO_LANGUAGE_FRANCES = 'fr-fr';
    public const TRILLONARIO_LANGUAGE_DEUTSCH = 'de-de';
    public const TRILLONARIO_LANGUAGE_PORTUGUESE = 'pt-la';

    public const LIST_LANGUAGES_USER_CODE = [
        [ 'id' => 0, 'code' => self::TRILLONARIO_LANGUAGE_SPANISH, 'name' => 'Español' ],
        [ 'id' => 1, 'code' => self::TRILLONARIO_LANGUAGE_ENGLISH, 'name' => 'English' ],
        [ 'id' => 2, 'code' => self::TRILLONARIO_LANGUAGE_FRANCES, 'name' => 'Français' ],
        [ 'id' => 3, 'code' => self::TRILLONARIO_LANGUAGE_DEUTSCH, 'name' => 'Deutsch' ],
        [ 'id' => 4, 'code' => 'pl-pl', 'name' => 'Polskie' ],
        [ 'id' => 5, 'code' => self::TRILLONARIO_LANGUAGE_PORTUGUESE, 'name' => 'Português' ],
    ];

    public const LOCALE_LANGUAGE_SPANISH   = 'es';
    public const LOCALE_LANGUAGE_ENGLISH   = 'en';
    public const LOCALE_LANGUAGE_PORTUGUES = 'pt';

    public const EXCEPT_COUNTRY_REGION_CASINO_SPORT_SCRATCH = [
        0 => [
            'iso_state'   => 'mb',
            'iso_country' => 'ca',
        ],
        1 => [
            'iso_state'   => 'qc',
            'iso_country' => 'ca',
        ],
    ];

    //CONST FREE-SPIN
    public const USE_COOKIE_FREE_SPIN                 = true;
    public const USE_LEVENSHTEIN_FREE_SPIN            = false;
    public const CHECK_IP_FREE_SPIN                   = true;
    public const CHECK_SCORE_FREE_SPIN                = true;
    public const CHECK_PROXY_FREE_SPIN                = false;
    public const FORBID_TRANSPARENT_PROXY_FREE_SPIN   = false;//true
    public const NO_FREE_MAIL_FREE_SPIN               = false;
    public const CHECK_COUNTRY_FREE_SPIN              = false;
    public const CHECK_NAME_FREE_SPIN                 = false;
    public const INTERVAL_HOUR_FREE_SPIN              = 1; //Hours to check for an ip if it has been used in users_login
    public const LEVENSHTEIN_MAX_NAME_VALUE_FREE_SPIN = 90;
    public const LEVENSHTEIN_MIN_NAME_VALUE_FREE_SPIN = 75;
    public const LEVENSHTEIN_ADDRESS_VALUE_FREE_SPIN  = 90;

    public const CACHE_TIME_DAY                       = 186400; //TTL
    public const CACHE_TIME_FIVE_MINUTES              = 300;    //TTL
    public const CACHE_TIME_TEN_MINUTES               = 600;    //TTL
    public const CACHE_NAME_EXCEPTION_NOTIFICATION    = 'rapi_errors';

    public const POSSIBLE_FRAUD_EXCEPTION_ERROR    = 'POSSIBLE FRAUD';
    public const PERMISSION_DENIED_EXCEPTION_ERROR = 'PERMISSION DENIED';
    public const INVALID_METHOD_EXCEPTION_ERROR    = 'INVALID METHOD';
    public const HTTP_EXCEPTION_ERROR              = 'HTTP EXCEPTION';
    public const QUERY_EXCEPTION_ERROR             = 'QUERY EXCEPTION';
    public const TOTAL_EXCEPTION_ERROR             = '500 ERROR';
    public const QUICK_DEPOSIT_ERROR               = 'QUICK DEPOSIT';

    public const LISTS_EXCEPTION_ERROR = [
        self::PERMISSION_DENIED_EXCEPTION_ERROR,
        self::INVALID_METHOD_EXCEPTION_ERROR,
        self::HTTP_EXCEPTION_ERROR,
        self::QUERY_EXCEPTION_ERROR,
        self::TOTAL_EXCEPTION_ERROR,
        self::QUICK_DEPOSIT_ERROR,
    ];

    public const NUMBER_ZERO          = 0;
    public const NUMBER_ONE           = 1;
    public const CURR_CODE_USD        = 'USD';
    public const MAX_LOTTO_ID_LOTTERY = 1000;

    public const REGISTER_FREE_SPIN = "REGISTER";

    public const LOGIN_FREE_SPIN = "LOGIN";

    public const KEY_CACHE_TOKEN_SSR = 'token-ssr-rapi';

    public const DOCUMENT_CC_COLOMBIAN_IDENTIFICATION_CARD = 1;
    public const DOCUMENT_CE_IMMIGRATION_CARD              = 2;
    public const DOCUMENT_TI_IDENTITY_CARD                 = 3;
    public const DOCUMENT_NIT_TAX_IDENTIFICATION_NUMBER    = 4;
    public const DOCUMENT_PPN_PASSPORT                     = 5;
    public const DOCUMENT_SSN_SOCIAL_SECURITY_NUMBER       = 6;

    public const LIST_TYPE_CARD_DOCUMENT_COLOMBIAN = [
        [
            'id'   => self::DOCUMENT_CC_COLOMBIAN_IDENTIFICATION_CARD,
            'name' => 'cc_colombian_identification_card',
        ],
        [
            'id'   => self::DOCUMENT_CE_IMMIGRATION_CARD,
            'name' => 'ce_immigration_card',
        ],
        [
            'id'   => self::DOCUMENT_NIT_TAX_IDENTIFICATION_NUMBER,
            'name' => 'nit_tax_identification_number',
        ],
        [
            'id'   => self::DOCUMENT_PPN_PASSPORT,
            'name' => 'ppn_passport',
        ],
        [
            'id'   => self::DOCUMENT_SSN_SOCIAL_SECURITY_NUMBER,
            'name' => 'ssn_social_security',
        ],
    ];

}
