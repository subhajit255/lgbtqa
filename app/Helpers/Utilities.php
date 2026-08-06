<?php

use App\Models\LogError;
use App\Models\Notification;
use App\Models\Role;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

if (! function_exists('isSluggable')) {
    function isSluggable($value)
    {
        return Str::slug($value);
    }
}

if (! function_exists('pageLimit')) {
    function pageLimit($limit = null)
    {
        if (is_null($limit)) {
            $limitCount = 10;
        } else {
            $limitCount = $limit;
        }

        return $limitCount;
    }
}

if (! function_exists('sidebarOpen')) {
    function sidebarOpen($routes = [])
    {
        $currRoute = Route::currentRouteName();
        $open = false;
        foreach ($routes as $route) {
            if (str_contains($route, '*')) {
                if (str_contains($currRoute, substr($route, 0, strpos($route, '*')))) {
                    $open = true;
                    break;
                }
            } else {
                if ($currRoute === $route) {
                    $open = true;
                    break;
                }
            }
        }

        return $open ? 'show' : '';
    }
}

if (! function_exists('sidebarActive')) {
    function sidebarActive($routes = [])
    {
        $currRoute = Route::currentRouteName();
        $open = false;
        foreach ($routes as $route) {
            if (str_contains($route, '*')) {
                if (str_contains($currRoute, substr($route, 0, strpos($route, '*')))) {
                    $open = true;
                    break;
                }
            } else {
                if ($currRoute === $route) {
                    $open = true;
                    break;
                }
            }
        }

        return $open ? 'active' : '';
    }
}

if (! function_exists('get_content')) {
    function get_content($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        ob_start();
        curl_exec($ch);
        curl_close($ch);
        $string = ob_get_contents();
        ob_end_clean();

        return $string;
    }
}

if (! function_exists('isMobileDevice')) {
    function isMobileDevice()
    {
        return preg_match(
            "/(android|avantgo|blackberry|bolt|boost|cricket|docomo
                            |fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i",
            $_SERVER['HTTP_USER_AGENT']
        );
    }
}

if (! function_exists('getStatusName')) {
    function getStatusName($status)
    {
        $returnData = 'In Active';
        if ($status == 1) {
            $returnData = 'Active';
        } elseif ($status == 3) {
            $returnData = 'Deleted';
        } elseif ($status == 4) {
            $returnData = 'Drafted';
        }

        return $returnData;
    }
}

if (! function_exists('getDayNumber')) {
    function getDayNumber($dayName)
    {
        $days = ['sunday' => 1, 'monday' => 2, 'tuesday' => 3, 'wednesday' => 4, 'thursday' => 5, 'friday' => 6, 'saturday' => 7];
        $currentDay = $days[$dayName];

        return $currentDay;
    }
}

if (! function_exists('uuidtoid')) {
    function uuidtoid($uuid, $table)
    {
        $dbDetails = DB::table($table)
            ->select('id')
            ->where('uuid', $uuid)->first();

        if ($dbDetails) {
            return $dbDetails->id;
        } else {
            abort(404);
        }
    }
}
if (! function_exists('idtouuid')) {
    function idtouuid($id, $table)
    {
        $dbDetails = DB::table($table)
            ->select('uuid')
            ->where('id', $id)->first();

        if ($dbDetails) {
            return $dbDetails->uuid;
        } else {
            abort(404);
        }
    }
}

if (! function_exists('safe_b64encode')) {
    function safe_b64encode($string)
    {
        $pretoken = '';
        $posttoken = '';

        $codealphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codealphabet .= 'abcdefghijklmnopqrstuvwxyz';
        $codealphabet .= '0123456789';
        $max = strlen($codealphabet); // edited

        for ($i = 0; $i < 3; $i++) {
            $pretoken .= $codealphabet[rand(0, $max - 1)];
        }

        for ($i = 0; $i < 3; $i++) {
            $posttoken .= $codealphabet[rand(0, $max - 1)];
        }

        $string = $pretoken . $string . $posttoken;
        $data = base64_encode($string);
        $data = str_replace(['+', '/', '='], ['-', '_', ''], $data);

        return $data;
    }
}

if (! function_exists('safe_b64decode')) {
    function safe_b64decode($string)
    {
        $data = str_replace(['-', '_'], ['+', '/'], $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }

        $data = base64_decode($data);
        $data = substr($data, 3);
        $data = substr($data, 0, -3);

        return $data;
    }
}

if (! function_exists('human_date')) {
    function human_date($date)
    {
        $now = Carbon::now();
        $date = Carbon::parse($date);

        return $date->diffForHumans($now);
    }
}

if (! function_exists('getCurrency')) {
    function getCurrency()
    {
        return '₹ ';
    }
}

if (! function_exists('generateOtp')) {
    function generateOtp($digit = 4)
    {
        $generator = '1357902468';
        $result = '';
        for ($i = 1; $i <= $digit; $i++) {
            $result .= substr($generator, (rand() % (strlen($generator))), 1);
        }

        return $result;
    }
}

if (! function_exists('getRoles')) {
    function getRoles()
    {
        $data = Role::where('is_active', 1)->whereNotIn('slug', ['super-admin'])->get();

        return $data;
    }
}

if (! function_exists('countUnReadNotificationSuperAdmin')) {
    function countUnReadNotificationSuperAdmin()
    {
        $allNotificationCount = Notification::where(['is_read' => 0, 'for' => 1])->count();
        if ($allNotificationCount) {
            return $allNotificationCount;
        } else {
            return 0;
        }
    }
}
if (! function_exists('listUnReadNotificationSuperAdmin')) {
    function listUnReadNotificationSuperAdmin()
    {
        $allNotification = Notification::where(['for' => 1, 'is_read' => 0])->latest()->get();
        if ($allNotification) {
            return $allNotification;
        } else {
            return [];
        }
    }
}

if (! function_exists('countUnReadChatsAdmin')) {
    function countUnReadChatsAdmin()
    {
        // Unread chat messages are stored as notifications with type 'chat_message'
        $allUnreadChatCount = \App\Models\Notification::where([
            'is_read' => 0,
            'for' => 1,
            'type' => 'chat_message'
        ])->count();

        if ($allUnreadChatCount) {
            return $allUnreadChatCount;
        } else {
            return 0;
        }
    }
}


if (! function_exists('getCountries')) {
    function getCountries()
    {
        $getCountries = file_get_contents(public_path('assets/json/countries.json'));
        $allCountries = json_decode($getCountries, true);

        return $allCountries['countries'];
    }
}

if (! function_exists('getLanguages')) {
    function getLanguages()
    {
        $getLanguages = file_get_contents(public_path('assets/json/languages.json'));
        return json_decode($getLanguages, true);
    }
}

if (! function_exists('getLanguageName')) {
    function getLanguageName($value)
    {
        if (empty($value)) {
            return '';
        }
        if (is_numeric($value)) {
            $oldLanguages = getLanguagesSpoken();
            if (isset($oldLanguages[$value])) {
                return $oldLanguages[$value];
            }
            $oldLearning = getLanguagesLearning();
            if (isset($oldLearning[$value])) {
                return $oldLearning[$value];
            }
        }

        $languages = getLanguages();
        foreach ($languages as $lang) {
            if (strcasecmp($lang['code'], $value) === 0 || strcasecmp($lang['name'], $value) === 0) {
                return $lang['name'];
            }
        }

        return $value;
    }
}

if (! function_exists('getNationalitiesList')) {
    function getNationalitiesList()
    {
        $getNationalities = file_get_contents(public_path('assets/json/nationalities.json'));
        return json_decode($getNationalities, true);
    }
}

if (! function_exists('getNationalityName')) {
    function getNationalityName($value)
    {
        if (empty($value)) {
            return '';
        }
        if (is_numeric($value)) {
            $oldNationalities = getNationalities();
            if (isset($oldNationalities[$value])) {
                return $oldNationalities[$value];
            }

            $newNationalities = getNationalitiesList();
            foreach ($newNationalities as $nat) {
                if ($nat['id'] == $value) {
                    return $nat['name'];
                }
            }
        }

        return $value;
    }
}


if (! function_exists('getStates')) {
    function getStates($countryId = null)
    {
        $getStates = file_get_contents(public_path('assets/json/states.json'));
        $allStates = json_decode($getStates, true);
        $states = $allStates['states'];

        if ($countryId !== null) {
            $states = array_filter($states, fn($state) => isset($state['country_id']) && $state['country_id'] == $countryId);
            // Re-index array if needed
            $states = array_values($states);
        }

        return $states;
    }
}

if (! function_exists('getCities')) {
    function getCities($stateId = null)
    {
        $getCities = file_get_contents(public_path('assets/json/cities.json'));
        $allCities = json_decode($getCities, true);
        $cities = $allCities['cities'];

        if ($stateId !== null) {
            $cities = array_filter($cities, fn($city) => isset($city['state_id']) && $city['state_id'] == $stateId);
            // Re-index array if needed
            $cities = array_values($cities);
        }

        return $cities;
    }
}

if (! function_exists('errorLogAndReturn')) {
    function errorLogAndReturn($th)
    {
        LogError::create([
            'message' => $th->getMessage(),
            'file_path' => $th->getFile(),
            'line_number' => $th->getLine(),
        ]);

        return [
            'Message' => $th->getMessage(),
            'File Path' => $th->getFile(),
            'Line Number' => $th->getLine(),
        ];
    }
}

if (! function_exists('getCurrentTimeZone')) {
    function getCurrentTimeZone()
    {
        // return date_default_timezone_get() ?? 'Asia/Kolkata';
        return 'Asia/Kolkata';
    }
}

if (! function_exists('getGender')) {
    function getGender()
    {
        return [
            1 => 'Man',
            2 => 'Woman',
            3 => 'Non-binary',
            4 => 'Trans Woman',
            5 => 'Trans Man',
            6 => 'Genderfluid',
            7 => 'Agender',
            8 => 'Genderqueer',
            9 => 'Bigender',
            10 => 'Demigirl',
            11 => 'Demiboy',
            12 => 'Two-spirit',
            13 => 'Other',
        ];
    }
}
if (! function_exists('getDatingPreferences')) {
    function getDatingPreferences()
    {
        return [
            1 => 'Men',
            2 => 'Women',
            3 => 'Non-binary & gender-diverse',
            4 => 'Open to Everyone',
        ];
    }
}

if (! function_exists('getOrientation')) {
    function getOrientation()
    {
        return [
            1 => 'Lesbian',
            2 => 'Gay',
            3 => 'Bisexual',
            4 => 'Pansexual',
            5 => 'Queer',
            6 => 'Straight',
            7 => 'Asexual',
            8 => 'Demisexual',
            9 => 'Gray-asexual',
            10 => 'Omnisexual',
            11 => 'Polysexual',
            12 => 'Androsexual',
            13 => 'Other',
        ];
    }
}

if (! function_exists('getAgeRanges')) {
    function getAgeRanges()
    {
        return [
            1 => '18-24',
            2 => '25-34',
            3 => '35-44',
            4 => '45+',
        ];
    }
}

if (! function_exists('getDistanceRanges')) {
    function getDistanceRanges()
    {
        return [
            1 => '00-84',
            2 => '90-120',
            3 => '140-200',
            4 => 'No limit',
        ];
    }
}

if (! function_exists('getRelationshipStatus')) {
    function getRelationshipStatus()
    {
        return [
            1 => 'Single',
            2 => 'Dating / seeing someone',
            3 => 'In a relationship',
            4 => 'Engaged',
            5 => 'Married',
            6 => 'Open relationship',
            7 => 'Polyamorous relationship',
            8 => 'Ethically non-monogamous (ENM)',
            9 => "It's complicated",
            10 => 'Separated',
            11 => 'Divorced',
            12 => 'Widowed',
        ];
    }
}

if (! function_exists('getWhatImLookingFor')) {
    function getWhatImLookingFor()
    {
        return [
            1 => 'Friends',
            2 => 'Dating',
            3 => 'Long-term relationship',
            4 => 'Poly dating',
            5 => 'Casual / nothing serious',
            6 => 'One-night stand / hookups',
            7 => 'Friendship+ / friends with benefits',
            8 => 'Community / meetups',
            9 => 'Networking',
            10 => 'Exploring / not sure yet',
            11 => 'Open to all',
        ];
    }
}

if (! function_exists('getBodyTypes')) {
    // Slim, Average, Athletic, Curvy, Muscular, Plus-size, Other / Self-describe, Prefer not to say
    function getBodyTypes()
    {
        return [
            1 => 'Slim',
            2 => 'Average',
            3 => 'Athletic',
            4 => 'Curvy',
            5 => 'Muscular',
            6 => 'Plus-size',
            7 => 'Other / Self-describe',
            8 => 'Prefer not to say',
        ];
    }
}

if (! function_exists('getEyeColors')) {
    // Brown, Blue, Green, Hazel, Grey, Other, Prefer not to say
    function getEyeColors()
    {
        return [
            1 => 'Brown',
            2 => 'Blue',
            3 => 'Green',
            4 => 'Hazel',
            5 => 'Grey',
            6 => 'Other',
            7 => 'Prefer not to say',
        ];
    }
}

if (! function_exists('getHairColors')) {
    // Black, Brown, Blonde, Red, Grey, Other/colorful, Bald, Prefer not to say
    function getHairColors()
    {
        return [
            1 => 'Black',
            2 => 'Brown',
            3 => 'Blonde',
            4 => 'Red',
            5 => 'Grey',
            6 => 'Other / colorful',
            7 => 'Bald',
            8 => 'Prefer not to say',
        ];
    }
}

if (! function_exists('getSexImportance')) {
    // Not Important, Somewhat important, Important, Very important, Prefer not to say
    function getSexImportance()
    {
        return [
            1 => 'Not Important',
            2 => 'Somewhat important',
            3 => 'Important',
            4 => 'Very important',
            5 => 'Prefer not to say',
        ];
    }
}

if (! function_exists('getRolePositions')) {
    // Top, Bottom, Versatile, Not applicable, Prefer not to say
    function getRolePositions()
    {
        return [
            1 => 'Top',
            2 => 'Bottom',
            3 => 'Versatile',
            4 => 'Not applicable',
            5 => 'Prefer not to say',
        ];
    }
}

if (! function_exists('getDatingPaces')) {
    // Slow burn, Open to casual, Open to serious, Depends on connection, Prefer not to say
    function getDatingPaces()
    {
        return [
            1 => 'Slow burn',
            2 => 'Open to casual',
            3 => 'Open to serious',
            4 => 'Depends on connection',
            5 => 'Prefer not to say',
        ];
    }
}

if (! function_exists('getPresentationPreferences')) {
    // Feminine - presenting, Masculine - presenting, Androgynous - mixed, No preference, Prefer not to say
    function getPresentationPreferences()
    {
        return [
            1 => 'Feminine - presenting',
            2 => 'Masculine - presenting',
            3 => 'Androgynous - mixed',
            4 => 'No preference',
            5 => 'Prefer not to say',
        ];
    }
}

if (! function_exists('getHobbyItemsByCategory')) {
    function getHobbyItemsByCategory($categoryTitle, $default = [])
    {
        try {
            $hobby = \App\Models\Hobby::with(['items' => function ($query) {
                $query->where('is_active', 1);
            }])->where('title', $categoryTitle)->first();

            if ($hobby && $hobby->items->count() > 0) {
                return $hobby->items->pluck('name', 'id')->toArray();
            }
        } catch (\Throwable $th) {
            // Fallback to default
        }
        return $default;
    }
}

if (! function_exists('getAlcohol')) {
    function getAlcohol()
    {
        return getHobbyItemsByCategory('Alcohol', [
            1 => 'Never',
            2 => 'Socially',
            3 => 'Sometimes',
            4 => 'Often',
            5 => 'Sober',
            6 => 'Prefer not to say',
        ]);
    }
}

if (! function_exists('getSmoking')) {
    function getSmoking()
    {
        return getHobbyItemsByCategory('Smoking / Nicotine', [
            1 => 'Non-Smoker',
            2 => 'Social smoker',
            3 => 'Smoker',
            4 => 'Vapes / nicotine',
            5 => 'Trying to quit',
            6 => 'Prefer not to say',
        ]);
    }
}

if (! function_exists('getExercise')) {
    function getExercise()
    {
        return getHobbyItemsByCategory('How often do you work out?', [
            1 => 'Daily',
            2 => 'Often',
            3 => 'Sometimes',
            4 => 'Rarely',
            5 => 'Never',
            6 => 'Prefer not to say',
        ]);
    }
}

if (! function_exists('getDiet')) {
    function getDiet()
    {
        return getHobbyItemsByCategory('Diet', [
            1 => 'Omnivore',
            2 => 'Vegetarian',
            3 => 'Vegan',
            4 => 'Pescatarian',
            5 => 'Flexitarian',
            6 => 'other / self-describe',
            7 => 'Prefer not to say',
        ]);
    }
}

if (! function_exists('getSleepRhythm')) {
    function getSleepRhythm()
    {
        return getHobbyItemsByCategory('Sleep rhythm', [
            1 => 'Early bird',
            2 => 'Night owl',
            3 => 'Depends / both',
            4 => 'Prefer not to say',
        ]);
    }
}

if (! function_exists('getKidsHave')) {
    function getKidsHave()
    {
        return getHobbyItemsByCategory('Do you have kids?', [
            1 => 'No kids',
            2 => 'Have kids (with me)',
            3 => 'Have kids (not with me)',
            4 => 'Prefer not to say',
        ]);
    }
}

if (! function_exists('getKidsFuture')) {
    function getKidsFuture()
    {
        return getHobbyItemsByCategory('Kids in the future?', [
            1 => 'Wants kids',
            2 => "Don't want kids",
            3 => 'Open on it',
            4 => 'Not sure',
            5 => 'Prefer not to say',
        ]);
    }
}

if (! function_exists('getPetsCurrent')) {
    function getPetsCurrent()
    {
        return getHobbyItemsByCategory('Current pets', [
            1 => 'Dog',
            2 => 'Cat',
            3 => 'Bird',
            4 => 'Fish',
            5 => 'Reptile',
            6 => 'Small pets',
            7 => 'other',
            8 => 'No pets',
        ]);
    }
}

if (! function_exists('getPetsFuture')) {
    function getPetsFuture()
    {
        return getHobbyItemsByCategory('Pets in future?', [
            1 => 'Want a pet',
            2 => "Don't want a pet",
            3 => 'Open on it',
            4 => 'Allergic',
            5 => 'Prefer not to say',
        ]);
    }
}

if (! function_exists('getLivingPreference')) {
    function getLivingPreference()
    {
        return getHobbyItemsByCategory('Where do you see yourself living?', [
            1 => 'City',
            2 => 'Small town',
            3 => 'Countryside',
            4 => 'Flexible',
            5 => 'Prefer not to say',
        ]);
    }
}

if (! function_exists('getTravelImportance')) {
    function getTravelImportance()
    {
        return getHobbyItemsByCategory('How important is travel to you?', [
            1 => 'Not Important',
            2 => 'Somewhat important',
            3 => 'Important',
            4 => 'Very important',
            5 => 'Prefer not to say',
        ]);
    }
}

if (! function_exists('getPreferredCommunication')) {
    function getPreferredCommunication()
    {
        return getHobbyItemsByCategory('Preferred communication', [
            1 => 'Texting',
            2 => 'Voice notes',
            3 => 'Calls',
            4 => 'Video call',
            5 => 'In person',
            6 => 'Prefer not to say',
        ]);
    }
}

if (! function_exists('getLoveLanguage')) {
    function getLoveLanguage()
    {
        return getHobbyItemsByCategory('Love language', [
            1 => 'Quality time',
            2 => 'Words of affirmation',
            3 => 'Acts of service',
            4 => 'Gift giving',
            5 => 'Physical touch',
        ]);
    }
}

if (! function_exists('getSocialEnergy')) {
    function getSocialEnergy()
    {
        return getHobbyItemsByCategory('Social energy', [
            1 => 'Introvert',
            2 => 'Extrovert',
            3 => 'Ambivert',
            4 => 'Prefer not to say',
        ]);
    }
}

if (! function_exists('getPersonalityType')) {
    function getPersonalityType()
    {
        return getHobbyItemsByCategory('Personality type (MBTI)', [
            1 => 'ISTJ/ISFJ/INFJ/INTJ',
            2 => 'ISTP/ISFP/INFP/INTP',
            3 => 'ESTP/ESFP/ENFP/ENTP',
            4 => 'ESTJ/ESFJ/ENFJ/ENTJ',
            5 => 'Not sure',
            6 => 'Prefer not to say',
        ]);
    }
}

if (! function_exists('getEducation')) {
    function getEducation()
    {
        return getHobbyItemsByCategory('Education', [
            1 => 'Apprenticeship',
            2 => 'High school',
            3 => 'Bachelor\'s',
            4 => 'Master\'s',
            5 => 'PhD',
            6 => 'Trade school',
            7 => 'Prefer not to say',
        ]);
    }
}

if (! function_exists('getHairLengths')) {
    function getHairLengths()
    {
        return getHobbyItemsByCategory('Hair length', [
            1 => 'Short',
            2 => 'Medium',
            3 => 'Long',
            4 => 'Shaved / Bald',
            5 => 'Other',
            6 => 'Prefer not to say',
        ]);
    }
}

if (! function_exists('getHairLength')) {
    function getHairLength()
    {
        return getHairLengths();
    }
}

if (! function_exists('getTattoos')) {
    function getTattoos()
    {
        return getHobbyItemsByCategory('Tattoos & piercings', [
            1 => 'None',
            2 => 'Tattoos',
            3 => 'Piercings',
            4 => 'Both',
            5 => 'Prefer not to say',
        ]);
    }
}

if (! function_exists('getTattoosAndPiercings')) {
    function getTattoosAndPiercings()
    {
        return getTattoos();
    }
}

if (! function_exists('getNationalities')) {
    function getNationalities()
    {
        return [
            1 => 'India',
            2 => 'United States',
            3 => 'United Kingdom',
            4 => 'Canada',
            5 => 'Australia',
            6 => 'France',
            7 => 'Germany',
            8 => 'Brazil',
            9 => 'Japan',
            10 => 'South Africa',
        ];
    }
}

if (! function_exists('getCitiesList')) {
    function getCitiesList()
    {
        return [
            1 => 'Mumbai',
            2 => 'Delhi',
            3 => 'London',
            4 => 'New York',
            5 => 'Paris',
            6 => 'Toronto',
            7 => 'Sydney',
            8 => 'Other',
        ];
    }
}

if (! function_exists('getLanguagesSpoken')) {
    function getLanguagesSpoken()
    {
        return [
            1 => 'English',
            2 => 'Hindi',
            3 => 'Spanish',
            4 => 'French',
            5 => 'Arabic',
            6 => 'Portuguese',
            7 => 'German',
            8 => 'Italian',
        ];
    }
}

if (! function_exists('getLanguagesLearning')) {
    function getLanguagesLearning()
    {
        return [
            1 => 'English',
            2 => 'Spanish',
            3 => 'French',
            4 => 'Japanese',
            5 => 'Korean',
            6 => 'Sign Language',
            7 => 'Mandarin',
            8 => 'Arabic',
        ];
    }
}

if (! function_exists('getComingOutStatuses')) {
    function getComingOutStatuses()
    {
        return [
            1 => 'Not out',
            2 => 'Out of a few',
            3 => 'Out to friend',
            4 => 'Out to family',
            5 => 'Prefer not to say',
        ];
    }
}

if (! function_exists('getReligions')) {
    function getReligions()
    {
        return [
            1 => 'Atheist',
            2 => 'Agnostic',
            3 => 'Spiritual',
            4 => 'Christian',
            5 => 'Muslim',
            6 => 'Jewish',
            7 => 'Hindu',
            8 => 'Buddhist',
            9 => 'Sikh',
            10 => 'Pagan / Wiccan',
            11 => 'Other / Self-describe',
            12 => 'Prefer not to say',
        ];
    }
}

if (! function_exists('getPoliticalViews')) {
    function getPoliticalViews()
    {
        return [
            1 => 'Progressive / left',
            2 => 'Center-left',
            3 => 'Center',
            4 => 'Center-right',
            5 => 'Conservative / right',
            6 => 'Right',
            7 => 'Apolitical',
            8 => 'Prefer not to say',
        ];
    }
}

if (! function_exists('getMusicTests')) {
    function getMusicTests()
    {
        return [
            1 => 'Pop',
            2 => 'Rock',
            3 => 'Hip Hop',
            4 => 'R&B',
            5 => 'Country',
            6 => 'Electronic',
            7 => 'Jazz',
            8 => 'Classical',
            9 => 'Other',
        ];
    }
}

if (! function_exists('getOccupations')) {
    function getOccupations()
    {
        return [
            1 => 'Student',
            2 => 'Artist / Creative',
            3 => 'Developer / Engineer',
            4 => 'Doctor / Healthcare',
            5 => 'Teacher / Educator',
            6 => 'Designer',
            7 => 'Writer / Journalist',
            8 => 'Entrepreneur / Founder',
            9 => 'Office Worker / Manager',
            10 => 'Sales / Marketing',
            11 => 'Other',
            12 => 'Prefer not to say',
        ];
    }
}

if (! function_exists('getZodiacs')) {
    function getZodiacs()
    {
        return [
            1 => 'Aries',
            2 => 'Taurus',
            3 => 'Gemini',
            4 => 'Cancer',
            5 => 'Leo',
            6 => 'Virgo',
            7 => 'Libra',
            8 => 'Scorpio',
            9 => 'Sagittarius',
            10 => 'Capricorn',
            11 => 'Aquarius',
            12 => 'Pisces',
        ];
    }
}
