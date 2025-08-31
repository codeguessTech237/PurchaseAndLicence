<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ActivationClass;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;


class InstallController extends Controller
{
    // Utilisation d’un trait ActivationClass (probablement pour la gestion des licences)
    use ActivationClass;

    // Étape 0 : affichage de la première vue d’installation
    public function step0()
    {
        return view('installation.step0');
    }

    // Étape 1 : Vérification des prérequis (extensions PHP, permissions, etc.)
    public function step1(Request $request)
    {
        if (Hash::check('step_1', $request['token'])) {
            // Vérifie si certaines extensions PHP et permissions sont activées
            $permission['curl_enabled'] = function_exists('curl_version');
            $permission['curl'] = function_exists('curl_version');
            $permission['bcmath'] = extension_loaded('bcmath');
            $permission['ctype'] = extension_loaded('ctype');
            $permission['json'] = extension_loaded('json');
            $permission['mbstring'] = extension_loaded('mbstring');
            $permission['openssl'] = extension_loaded('openssl');
            $permission['pdo'] = defined('PDO::ATTR_DRIVER_NAME');
            $permission['tokenizer'] = extension_loaded('tokenizer');
            $permission['xml'] = extension_loaded('xml');
            $permission['zip'] = extension_loaded('zip');
            $permission['fileinfo'] = extension_loaded('fileinfo');
            $permission['gd'] = extension_loaded('gd');
            $permission['sodium'] = extension_loaded('sodium');
            $permission['pdo_mysql'] = extension_loaded('pdo_mysql');
            // Vérifie que certains fichiers sont inscriptibles
            $permission['db_file_write_perm'] = is_writable(base_path('.env'));
            $permission['routes_file_write_perm'] = is_writable(base_path('app/Providers/RouteServiceProvider.txt'));

            // Retourne la vue avec les permissions
            return view('installation.step1', compact('permission'));
        }
        // Si token invalide
        session()->flash('error', 'Access denied!');
        return to_route('step0');
    }

    // Étape 2
    public function step2(Request $request)
    {
        if (Hash::check('step_2', $request['token'])) {
            return view('installation.step2');
        }
        session()->flash('error', 'Access denied!');
        return to_route('step0');
    }

    // Étape 3
    public function step3(Request $request)
    {
        if (Hash::check('step_3', $request['token'])) {
            return view('installation.step3');
        }
        session()->flash('error', 'Access denied!');
        return to_route('step0');
    }

    // Étape 4
    public function step4(Request $request)
    {
        if (Hash::check('step_4', $request['token'])) {
            return view('installation.step4');
        }
        session()->flash('error', 'Access denied!');
        return to_route('step0');
    }

    // Étape 5
    public function step5(Request $request)
    {
        if (Hash::check('step_5', $request['token'])) {
            return view('installation.step5', ['telCodes' => TELEPHONE_CODES]);
        }
        session()->flash('error', 'Access denied!');
        return to_route('step0');
    }

    // Vérification du code d’achat (licence du logiciel)
    public function purchase_code(Request $request)
    {
        // Prépare les infos de l’utilisateur et du domaine
        // return to_route('dmvf', ['purchase_key' => $request['purchase_key'], 'username' => $request['username']]);
        $post = [
            'name' => $request['name'],
            'email' => $request['email'],
            'username' => $request['username'],
            'purchase_key' => $request['purchase_key'],
            'domain' => preg_replace("#^[^:/.]*[:/]+#i", "", url('/')),
        ];
        // Vérifie la validité via dmvf()
        $response = $this->dmvf($post);

        // Redirige vers l’étape 3
        return redirect($response . '?token=' . bcrypt('step_3'));
    }

    // Étape système : création d’un utilisateur admin et paramétrage initial
    public function system_settings(Request $request)
    {
        // Vérifie le token
        if (!Hash::check('step_6', $request['token'])) {
            session()->flash('error', 'Access denied!');
            return to_route('step0');
        }

        // Validation du mot de passe et confirmation
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'same:confirm_password'],
            'confirm_password' => 'required',
        ]);
        if ($validator->fails()) {
            session()->flash('error', 'Confirm password does not match!');
            return back();
        }

        // ⚠️ Insertion admin et business_settings désactivée (commentée)

        /*DB::table('admins')->insertOrIgnore([
            'f_name' => $request['f_name'],
            'l_name' => $request['l_name'],
            'email' => $request['email'],
            'role_id' => 1,
            'password' => bcrypt($request['password']),
            'phone' => $request['phone_code'].$request['phone'],
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('business_settings')->where(['key' => 'business_name'])->update([
            'value' => $request['business_name']
        ]);*/

        // Mise à jour du fichier RouteServiceProvider
        $previousRouteServiceProvider = base_path('app/Providers/RouteServiceProvider.php');
        $newRouteServiceProvider = base_path('app/Providers/RouteServiceProvider.txt');
        copy($newRouteServiceProvider, $previousRouteServiceProvider);

        //sleep(5);
        //Helpers::remove_dir('storage/app/public');
        //Storage::disk('public')->makeDirectory('/');

        try {
            // Extraction d’une archive (commentée)
            //Madzipper::make('installation/backup/public.zip')->extractTo('storage/app');
        } catch (\Exception $exception) {
            info($exception);
        }

        return view('installation.step6');
    }

    // Installation de la base de données
    public function database_installation(Request $request)
    {
        // Vérifie la connexion DB
        if (self::check_database_connection($request->DB_HOST, $request->DB_DATABASE, $request->DB_USERNAME, $request->DB_PASSWORD)) {

            // Génère une nouvelle clé APP_KEY
            $key = base64_encode(random_bytes(32));

            $output =
                "APP_NAME=codeguess" . time() . "\n" .
                "APP_ENV=live\n" .
                "APP_KEY=base64:" . $key . "\n" .
                "APP_DEBUG=false\n" .
                "APP_INSTALL=true\n" .
                "APP_LOG_LEVEL=debug\n" .
                "APP_MODE=live\n" .
                "APP_URL=" . URL::to('/') . "\n\n" .

                "DB_CONNECTION=mysql\n" .
                "DB_HOST=" . $request->DB_HOST . "\n" .
                "DB_PORT=3306\n" .
                "DB_DATABASE=" . $request->DB_DATABASE . "\n" .
                "DB_USERNAME=" . $request->DB_USERNAME . "\n" .
                "DB_PASSWORD=\"" . $request->DB_PASSWORD . "\"\n\n" .

                "BROADCAST_DRIVER=log\n" .
                "CACHE_DRIVER=database\n" .
                "SESSION_DRIVER=file\n" .
                "SESSION_LIFETIME=120\n" .
                "QUEUE_DRIVER=sync\n\n" .

                "REDIS_HOST=127.0.0.1\n" .
                "REDIS_PASSWORD=null\n" .
                "REDIS_PORT=6379\n\n" .

                "PUSHER_APP_ID=\n" .
                "PUSHER_APP_KEY=\n" .
                "PUSHER_APP_SECRET=\n" .
                "PUSHER_APP_CLUSTER=mt1\n\n" .

                "PURCHASE_CODE=" . session('purchase_key') . "\n" .
                "BUYER_USERNAME=" . session('username') . "\n" .
                "SOFTWARE_ID=MzM1NzE3NTA=\n\n" .

                "SOFTWARE_VERSION=8.2\n" .
                "REACT_APP_KEY=43218516\n";

            // Écriture dans le fichier .env
            $file = fopen(base_path('.env'), 'w');
            fwrite($file, $output);
            fclose($file);

            // Vérifie que le fichier existe
            $path = base_path('.env');
            if (file_exists($path)) {
                return to_route('step4', ['token' => $request['token']]);
            } else {
                session()->flash('error', 'Database error!');
                return to_route('step3', ['token' => bcrypt('step_3')]);
            }
        } else {
            session()->flash('error', 'Database host error!');
            return to_route('step3', ['token' => bcrypt('step_3')]);
        }
    }

    // Importation du fichier SQL
    public function import_sql()
    {
        try {
            // Import manuel désactivé (commenté)
            //$sql_path = base_path('installation/backup/database.sql');
            //DB::unprepared(file_get_contents($sql_path));
            // version_7.9.1
            //Artisan::call('cache:table');

            return to_route('step5', ['token' => bcrypt('step_5')]);
        } catch (\Exception $exception) {
            session()->flash('error', 'Your database is not clean, do you want to clean database then import?');
            return back();
        }
    }

    // Forcer l’importation SQL (supprime la DB avant)
    public function force_import_sql()
    {
        try {
            Artisan::call('db:wipe', ['--force' => true]);
            // Import SQL désactivé (commenté)
            //$sql_path = base_path('installation/backup/database.sql');
            //DB::unprepared(file_get_contents($sql_path));
            // version_7.9.1
            //Artisan::call('cache:table');
            return to_route('step5', ['token' => bcrypt('step_5')]);
        } catch (\Exception $exception) {
            session()->flash('error', 'Check your database permission!');
            return back();
        }
    }

    // Vérification connexion à la base de données (mysqli)
    function check_database_connection($db_host = "", $db_name = "", $db_user = "", $db_pass = ""): bool
    {
        try {
            if (@mysqli_connect($db_host, $db_user, $db_pass, $db_name)) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            return false;
        }
    }
}


const TELEPHONE_CODES = [
    ["name" => 'UK (+44)', "code" => '+44'],
    ["name" => 'USA (+1)', "code" => '+1'],
    ["name" => 'Algeria (+213)', "code" => '+213'],
    ["name" => 'Andorra (+376)', "code" => '+376'],
    ["name" => 'Angola (+244)', "code" => '+244'],
    ["name" => 'Anguilla (+1264)', "code" => '+1264'],
    ["name" => 'Antigua & Barbuda (+1268)', "code" => '+1268'],
    ["name" => 'Argentina (+54)', "code" => '+54'],
    ["name" => 'Armenia (+374)', "code" => '+374'],
    ["name" => 'Aruba (+297)', "code" => '+297'],
    ["name" => 'Australia (+61)', "code" => '+61'],
    ["name" => 'Austria (+43)', "code" => '+43'],
    ["name" => 'Azerbaijan (+994)', "code" => '+994'],
    ["name" => 'Bahamas (+1242)', "code" => '+1242'],
    ["name" => 'Bahrain (+973)', "code" => '+973'],
    ["name" => 'Bangladesh (+880)', "code" => '+880'],
    ["name" => 'Barbados (+1246)', "code" => '+1246'],
    ["name" => 'Belarus (+375)', "code" => '+375'],
    ["name" => 'Belgium (+32)', "code" => '+32'],
    ["name" => 'Belize (+501)', "code" => '+501'],
    ["name" => 'Benin (+229)', "code" => '+229'],
    ["name" => 'Bermuda (+1441)', "code" => '+1441'],
    ["name" => 'Bhutan (+975)', "code" => '+975'],
    ["name" => 'Bolivia (+591)', "code" => '+591'],
    ["name" => 'Bosnia Herzegovina (+387)', "code" => '+387'],
    ["name" => 'Botswana (+267)', "code" => '+267'],
    ["name" => 'Brazil (+55)', "code" => '+55'],
    ["name" => 'Brunei (+673)', "code" => '+673'],
    ["name" => 'Bulgaria (+359)', "code" => '+359'],
    ["name" => 'Burkina Faso (+226)', "code" => '+226'],
    ["name" => 'Burundi (+257)', "code" => '+257'],
    ["name" => 'Cambodia (+855)', "code" => '+855'],
    ["name" => 'Cameroon (+237)', "code" => '+237'],
    ["name" => 'Canada (+1)', "code" => '+1'],
    ["name" => 'Cape Verde Islands (+238)', "code" => '+238'],
    ["name" => 'Cayman Islands (+1345)', "code" => '+1345'],
    ["name" => 'Central African Republic (+236)', "code" => '+236'],
    ["name" => 'Chile (+56)', "code" => '+56'],
    ["name" => 'China (+86)', "code" => '+86'],
    ["name" => 'Colombia (+57)', "code" => '+57'],
    ["name" => 'Comoros (+269)', "code" => '+269'],
    ["name" => 'Congo (+242)', "code" => '+242'],
    ["name" => 'Cook Islands (+682)', "code" => '+682'],
    ["name" => 'Costa Rica (+506)', "code" => '+506'],
    ["name" => 'Croatia (+385)', "code" => '+385'],
    ["name" => 'Cuba (+53)', "code" => '+53'],
    ["name" => 'Cyprus North (+90392)', "code" => '+90392'],
    ["name" => 'Cyprus South (+357)', "code" => '+357'],
    ["name" => 'Czech Republic (+42)', "code" => '+42'],
    ["name" => 'Denmark (+45)', "code" => '+45'],
    ["name" => 'Djibouti (+253)', "code" => '+253'],
    ["name" => 'Dominica (+1767)', "code" => '+1767'],
    ["name" => 'Dominican Republic (+1809)', "code" => '+1809'],
    ["name" => 'Ecuador (+593)', "code" => '+593'],
    ["name" => 'Egypt (+20)', "code" => '+20'],
    ["name" => 'El Salvador (+503)', "code" => '+503'],
    ["name" => 'Equatorial Guinea (+240)', "code" => '+240'],
    ["name" => 'Eritrea (+291)', "code" => '+291'],
    ["name" => 'Estonia (+372)', "code" => '+372'],
    ["name" => 'Ethiopia (+251)', "code" => '+251'],
    ["name" => 'Falkland Islands (+500)', "code" => '+500'],
    ["name" => 'Faroe Islands (+298)', "code" => '+298'],
    ["name" => 'Fiji (+679)', "code" => '+679'],
    ["name" => 'Finland (+358)', "code" => '+358'],
    ["name" => 'France (+33)', "code" => '+33'],
    ["name" => 'French Guiana (+594)', "code" => '+594'],
    ["name" => 'French Polynesia (+689)', "code" => '+689'],
    ["name" => 'Gabon (+241)', "code" => '+241'],
    ["name" => 'Gambia (+220)', "code" => '+220'],
    ["name" => 'Georgia (+7880)', "code" => '+7880'],
    ["name" => 'Germany (+49)', "code" => '+49'],
    ["name" => 'Ghana (+233)', "code" => '+233'],
    ["name" => 'Gibraltar (+350)', "code" => '+350'],
    ["name" => 'Greece (+30)', "code" => '+30'],
    ["name" => 'Greenland (+299)', "code" => '+299'],
    ["name" => 'Grenada (+1473)', "code" => '+1473'],
    ["name" => 'Guadeloupe (+590)', "code" => '+590'],
    ["name" => 'Guam (+671)', "code" => '+671'],
    ["name" => 'Guatemala (+502)', "code" => '+502'],
    ["name" => 'Guinea (+224)', "code" => '+224'],
    ["name" => 'Guinea - Bissau (+245)', "code" => '+245'],
    ["name" => 'Guyana (+592)', "code" => '+592'],
    ["name" => 'Haiti (+509)', "code" => '+509'],
    ["name" => 'Honduras (+504)', "code" => '+504'],
    ["name" => 'Hong Kong (+852)', "code" => '+852'],
    ["name" => 'Hungary (+36)', "code" => '+36'],
    ["name" => 'Iceland (+354)', "code" => '+354'],
    ["name" => 'India (+91)', "code" => '+91'],
    ["name" => 'Indonesia (+62)', "code" => '+62'],
    ["name" => 'Iran (+98)', "code" => '+98'],
    ["name" => 'Iraq (+964)', "code" => '+964'],
    ["name" => 'Ireland (+353)', "code" => '+353'],
    ["name" => 'Israel (+972)', "code" => '+972'],
    ["name" => 'Italy (+39)', "code" => '+39'],
    ["name" => 'Jamaica (+1876)', "code" => '+1876'],
    ["name" => 'Japan (+81)', "code" => '+81'],
    ["name" => 'Jordan (+962)', "code" => '+962'],
    ["name" => 'Kazakhstan (+7)', "code" => '+7'],
    ["name" => 'Kenya (+254)', "code" => '+254'],
    ["name" => 'Kiribati (+686)', "code" => '+686'],
    ["name" => 'Korea North (+850)', "code" => '+850'],
    ["name" => 'Korea South (+82)', "code" => '+82'],
    ["name" => 'Kuwait (+965)', "code" => '+965'],
    ["name" => 'Kyrgyzstan (+996)', "code" => '+996'],
    ["name" => 'Laos (+856)', "code" => '+856'],
    ["name" => 'Latvia (+371)', "code" => '+371'],
    ["name" => 'Lebanon (+961)', "code" => '+961'],
    ["name" => 'Lesotho (+266)', "code" => '+266'],
    ["name" => 'Liberia (+231)', "code" => '+231'],
    ["name" => 'Libya (+218)', "code" => '+218'],
    ["name" => 'Liechtenstein (+417)', "code" => '+417'],
    ["name" => 'Lithuania (+370)', "code" => '+370'],
    ["name" => 'Luxembourg (+352)', "code" => '+352'],
    ["name" => 'Macao (+853)', "code" => '+853'],
    ["name" => 'Macedonia (+389)', "code" => '+389'],
    ["name" => 'Madagascar (+261)', "code" => '+261'],
    ["name" => 'Malawi (+265)', "code" => '+265'],
    ["name" => 'Malaysia (+60)', "code" => '+60'],
    ["name" => 'Maldives (+960)', "code" => '+960'],
    ["name" => 'Mali (+223)', "code" => '+223'],
    ["name" => 'Malta (+356)', "code" => '+356'],
    ["name" => 'Marshall Islands (+692)', "code" => '+692'],
    ["name" => 'Martinique (+596)', "code" => '+596'],
    ["name" => 'Mauritania (+222)', "code" => '+222'],
    ["name" => 'Mayotte (+269)', "code" => '+269'],
    ["name" => 'Mexico (+52)', "code" => '+52'],
    ["name" => 'Micronesia (+691)', "code" => '+691'],
    ["name" => 'Moldova (+373)', "code" => '+373'],
    ["name" => 'Monaco (+377)', "code" => '+377'],
    ["name" => 'Montserrat (+1664)', "code" => '+1664'],
    ["name" => 'Morocco (+212)', "code" => '+212'],
    ["name" => 'Mozambique (+258)', "code" => '+258'],
    ["name" => 'Myanmar (+95)', "code" => '+95'],
    ["name" => 'Namibia (+264)', "code" => '+264'],
    ["name" => 'Nauru (+674)', "code" => '+674'],
    ["name" => 'Nepal (+977)', "code" => '+977'],
    ["name" => 'Netherlands (+31)', "code" => '+31'],
    ["name" => 'New Caledonia (+687)', "code" => '+687'],
    ["name" => 'New Zealand (+64)', "code" => '+64'],
    ["name" => 'Nicaragua (+505)', "code" => '+505'],
    ["name" => 'Niger (+227)', "code" => '+227'],
    ["name" => 'Nigeria (+234)', "code" => '+234'],
    ["name" => 'Niue (+683)', "code" => '+683'],
    ["name" => 'Norfolk Islands (+672)', "code" => '+672'],
    ["name" => 'Northern Marianas (+670)', "code" => '+670'],
    ["name" => 'Norway (+47)', "code" => '+47'],
    ["name" => 'Oman (+968)', "code" => '+968'],
    ["name" => 'Palau (+680)', "code" => '+680'],
    ["name" => 'Panama (+507)', "code" => '+507'],
    ["name" => 'Papua New Guinea (+675)', "code" => '+675'],
    ["name" => 'Paraguay (+595)', "code" => '+595'],
    ["name" => 'Peru (+51)', "code" => '+51'],
    ["name" => 'Philippines (+63)', "code" => '+63'],
    ["name" => 'Poland (+48)', "code" => '+48'],
    ["name" => 'Portugal (+351)', "code" => '+351'],
    ["name" => 'Qatar (+974)', "code" => '+974'],
    ["name" => 'Reunion (+262)', "code" => '+262'],
    ["name" => 'Romania (+40)', "code" => '+40'],
    ["name" => 'Russia (+7)', "code" => '+7'],
    ["name" => 'Rwanda (+250)', "code" => '+250'],
    ["name" => 'San Marino (+378)', "code" => '+378'],
    ["name" => 'Sao Tome & Principe (+239)', "code" => '+239'],
    ["name" => 'Saudi Arabia (+966)', "code" => '+966'],
    ["name" => 'Senegal (+221)', "code" => '+221'],
    ["name" => 'Serbia (+381)', "code" => '+381'],
    ["name" => 'Seychelles (+248)', "code" => '+248'],
    ["name" => 'Sierra Leone (+232)', "code" => '+232'],
    ["name" => 'Singapore (+65)', "code" => '+65'],
    ["name" => 'Slovak Republic (+421)', "code" => '+421'],
    ["name" => 'Slovenia (+386)', "code" => '+386'],
    ["name" => 'Solomon Islands (+677)', "code" => '+677'],
    ["name" => 'Somalia (+252)', "code" => '+252'],
    ["name" => 'South Africa (+27)', "code" => '+27'],
    ["name" => 'Spain (+34)', "code" => '+34'],
    ["name" => 'Sri Lanka (+94)', "code" => '+94'],
    ["name" => 'St. Helena (+290)', "code" => '+290'],
    ["name" => 'St. Kitts (+1869)', "code" => '+1869'],
    ["name" => 'St. Lucia (+1758)', "code" => '+1758'],
    ["name" => 'Sudan (+249)', "code" => '+249'],
    ["name" => 'Suriname (+597)', "code" => '+597'],
    ["name" => 'Swaziland (+268)', "code" => '+268'],
    ["name" => 'Sweden (+46)', "code" => '+46'],
    ["name" => 'Switzerland (+41)', "code" => '+41'],
    ["name" => 'Syria (+963)', "code" => '+963'],
    ["name" => 'Taiwan (+886)', "code" => '+886'],
    ["name" => 'Tajikstan (+7)', "code" => '+7'],
    ["name" => 'Thailand (+66)', "code" => '+66'],
    ["name" => 'Togo (+228)', "code" => '+228'],
    ["name" => 'Tonga (+676)', "code" => '+676'],
    ["name" => 'Trinidad & Tobago (+1868)', "code" => '+1868'],
    ["name" => 'Tunisia (+216)', "code" => '+216'],
    ["name" => 'Turkey (+90)', "code" => '+90'],
    ["name" => 'Turkmenistan (+7)', "code" => '+7'],
    ["name" => 'Turkmenistan (+993)', "code" => '+993'],
    ["name" => 'Turks & Caicos Islands (+1649)', "code" => '+1649'],
    ["name" => 'Tuvalu (+688)', "code" => '+688'],
    ["name" => 'Uganda (+256)', "code" => '+256'],
    ["name" => 'Ukraine (+380)', "code" => '+380'],
    ["name" => 'United Arab Emirates (+971)', "code" => '+971'],
    ["name" => 'Uruguay (+598)', "code" => '+598'],
    ["name" => 'Uzbekistan (+7)', "code" => '+7'],
    ["name" => 'Vanuatu (+678)', "code" => '+678'],
    ["name" => 'Vatican City (+379)', "code" => '+379'],
    ["name" => 'Venezuela (+58)', "code" => '+58'],
    ["name" => 'Vietnam (+84)', "code" => '+84'],
    ["name" => 'Virgin Islands - British (+1284)', "code" => '+1284'],
    ["name" => 'Virgin Islands - US (+1340)', "code" => '+1340'],
    ["name" => 'Wallis & Futuna (+681)', "code" => '+681'],
    ["name" => 'Yemen (North)(+969)', "code" => '+969'],
    ["name" => 'Yemen (South)(+967)', "code" => '+967'],
    ["name" => 'Zambia (+260)', "code" => '+260'],
    ["name" => 'Zimbabwe (+263)', "code" => '+263'],
];
