@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            User Profile Detail</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.user.list') }}">Users</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">View</li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        <a href="{{ route('admin.user.list') }}" class="btn btn-sm fw-bold btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to List
                        </a>
                        <a href="{{ route('admin.user.add', $detail->uuid) }}" class="btn btn-sm fw-bold btn-primary">
                            <i class="fas fa-edit me-1"></i> Edit User
                        </a>
                    </div>
                </div>
            </div>
            <!--end::Toolbar-->

            <!--begin::Content-->
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <div class="d-flex flex-column flex-xl-row">
                        <!--begin::Sidebar-->
                        <div class="flex-column flex-lg-row-auto w-100 w-xl-350px mb-10">
                            <div class="card mb-5 mb-xl-8 glass-card border-0 shadow-sm">
                                <div class="card-body pt-15">
                                    <div class="d-flex flex-center flex-column mb-5">
                                        <div class="symbol symbol-150px symbol-circle mb-7 shadow position-relative">
                                            <img src="{{ $detail->image_path }}" alt="image" />
                                            <span class="position-absolute bottom-0 end-0 {{ $detail->is_online ? 'bg-success' : 'bg-secondary' }} border border-4 border-body h-25px w-25px rounded-circle me-2 mb-2"
                                                title="{{ $detail->is_online ? 'Online' : 'Offline' }}"></span>
                                        </div>
                                        <a href="#"
                                            class="fs-3 text-gray-800 text-hover-primary fw-bold mb-1">
                                            {{ $detail->name }}
                                            @if ($detail->is_verified_email == 1)
                                                <i title="Email Verified" class="fas fa-check-circle text-success ms-1"></i>
                                            @endif
                                        </a>
                                        <div class="fs-5 fw-semibold text-muted mb-4 copy-original-password"
                                            data-password="{{ $detail->original_password ?? '' }}"
                                            style="cursor: pointer;"
                                            title="Double click to copy password">
                                            @<span>{{ $detail->username ?? 'no-username' }}</span></div>
                                        <div class="mb-4">
                                            @if ($detail->is_online)
                                                <span class="badge badge-light-success fw-bold px-3 py-2"><i class="fas fa-circle text-success fs-9 me-1"></i> Online</span>
                                            @else
                                                <span class="badge badge-light-secondary fw-bold px-3 py-2"><i class="fas fa-circle text-secondary fs-9 me-1"></i> Offline</span>
                                            @endif
                                        </div>
                                        <div class="mb-6">
                                            <span class="badge badge-light-primary fw-bold px-4 py-3">
                                                <i class="fas fa-venus-mars me-2"></i>
                                                {{ getGender()[$detail->profile?->gender] ?? 'N/A' }}
                                            </span>
                                        </div>

                                        <div class="d-flex flex-wrap flex-center">
                                            <div
                                                class="border border-gray-300 border-dashed rounded py-3 px-3 mb-3 text-center min-w-80px">
                                                <div class="fs-4 fw-bold text-gray-700">
                                                    {{ $detail->profile?->age ?? 'N/A' }}</div>
                                                <div class="fw-semibold text-muted">Age</div>
                                            </div>
                                            <div
                                                class="border border-gray-300 border-dashed rounded py-3 px-3 mx-4 mb-3 text-center min-w-80px">
                                                <div class="fs-4 fw-bold text-gray-700">{{ $detail->galleries->count() }}
                                                </div>
                                                <div class="fw-semibold text-muted">Photos</div>
                                            </div>
                                            <div
                                                class="border border-gray-300 border-dashed rounded py-3 px-3 mb-3 text-center min-w-80px">
                                                <div class="fs-4 fw-bold text-gray-700">{{ $detail->hobbies->count() }}
                                                </div>
                                                <div class="fw-semibold text-muted">Hobbies</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="separator separator-dashed my-3"></div>

                                    <div class="pb-5 fs-6">
                                        <div class="fw-bold mt-5 mb-2"><i class="fas fa-id-badge me-2 text-primary"></i>
                                            Account Summary</div>
                                        <div class="text-gray-600 mb-1"><span class="fw-semibold">User ID:</span>
                                            {{ $detail->id }}</div>
                                        <div class="text-gray-600 mb-1"><span class="fw-semibold">Status:</span>
                                            @if ($detail->is_active == 1)
                                                <span class="badge badge-light-success px-2 py-1">Active</span>
                                            @else
                                                <span class="badge badge-light-danger px-2 py-1">Inactive</span>
                                            @endif
                                        </div>
                                        <div class="text-gray-600 mb-1"><span class="fw-semibold">Joined:</span>
                                            {{ \Carbon\Carbon::parse($detail->created_at)->format('jS M Y') }}</div>
                                        <div class="text-gray-600 text-break"><span class="fw-semibold">UUID:</span>
                                            <small>{{ $detail->uuid }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Sidebar-->

                        <!--begin::Main Content-->
                        <div class="flex-lg-row-fluid ms-lg-15">
                            <!--begin::Nav Tabs Header-->
                            <div class="card glass-card border-0 shadow-sm mb-8">
                                <div class="card-body py-4 px-6">
                                    <ul
                                        class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-n2">
                                        <li class="nav-item">
                                            <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab"
                                                href="#kt_user_view_overview_tab">
                                                <i class="fas fa-user-circle me-2"></i> Overview
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                                                href="#kt_user_view_prefs_tab">
                                                <i class="fas fa-sliders-h me-2"></i> Preferences & Privacy
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                                                href="#kt_user_view_gallery_tab">
                                                <i class="fas fa-images me-2"></i> Photo Gallery
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                                                href="#kt_user_view_friends_tab">
                                                <i class="fas fa-user-friends me-2"></i> Friends
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                                                href="#kt_user_view_blocked_tab">
                                                <i class="fas fa-user-lock me-2"></i> Blocked Users
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!--end::Nav Tabs Header-->

                            <div class="tab-content" id="myTabContent">
                                 @php
                                         // Safely decode dating_preferences
                                         $datingPreferencesIds = [];
                                         if (!empty($detail->profile?->dating_preferences)) {
                                             if (is_array($detail->profile->dating_preferences)) {
                                                 $datingPreferencesIds = $detail->profile->dating_preferences;
                                             } else {
                                                 $decoded = json_decode($detail->profile->dating_preferences, true);
                                                 $datingPreferencesIds = is_array($decoded) ? $decoded : array_filter(explode(',', $detail->profile->dating_preferences));
                                             }
                                         }
                                         $datingPrefLabels = [];
                                         foreach ($datingPreferencesIds as $id) {
                                             if (isset(getDatingPreferences()[$id])) {
                                                 $datingPrefLabels[] = getDatingPreferences()[$id];
                                             }
                                         }
                                         $datingPrefText = !empty($datingPrefLabels) ? implode(', ', $datingPrefLabels) : 'N/A';

                                          // Safely decode languages_spoken
                                          $languagesSpoken = [];
                                          if (!empty($detail->profile?->languages_spoken)) {
                                              if (is_array($detail->profile->languages_spoken)) {
                                                  $languagesSpoken = $detail->profile->languages_spoken;
                                              } else {
                                                  $decoded = json_decode($detail->profile->languages_spoken, true);
                                                  $languagesSpoken = is_array($decoded) ? $decoded : array_filter(explode(',', $detail->profile->languages_spoken));
                                              }
                                          }
                                          $languagesSpokenLabels = [];
                                          foreach ($languagesSpoken as $val) {
                                              $languagesSpokenLabels[] = getLanguageName($val);
                                          }
                                          $languagesSpokenText = !empty($languagesSpokenLabels) ? implode(', ', $languagesSpokenLabels) : 'N/A';

                                          // Safely decode languages_learning
                                          $languagesLearning = [];
                                          if (!empty($detail->profile?->languages_learning)) {
                                              if (is_array($detail->profile->languages_learning)) {
                                                  $languagesLearning = $detail->profile->languages_learning;
                                              } else {
                                                  $decoded = json_decode($detail->profile->languages_learning, true);
                                                  $languagesLearning = is_array($decoded) ? $decoded : array_filter(explode(',', $detail->profile->languages_learning));
                                              }
                                          }
                                          $languagesLearningLabels = [];
                                          foreach ($languagesLearning as $val) {
                                              $languagesLearningLabels[] = getLanguageName($val);
                                          }
                                          $languagesLearningText = !empty($languagesLearningLabels) ? implode(', ', $languagesLearningLabels) : 'N/A';

                                          // Safely decode nationality
                                          $nationalityText = 'N/A';
                                          if (!empty($detail->profile?->nationality)) {
                                              $nationalityText = getNationalityName($detail->profile->nationality);
                                          }

                                          // Safely decode coming_out_status
                                          $comingOutText = 'N/A';
                                          if (!empty($detail->profile?->coming_out_status)) {
                                              $val = $detail->profile->coming_out_status;
                                              if (is_numeric($val) && isset(getComingOutStatuses()[$val])) {
                                                  $comingOutText = getComingOutStatuses()[$val];
                                              } else {
                                                  $comingOutText = $val;
                                              }
                                          }

                                          // Safely decode religion
                                          $religionIds = [];
                                          if (!empty($detail->profile?->religion)) {
                                              if (is_array($detail->profile->religion)) {
                                                  $religionIds = $detail->profile->religion;
                                              } else {
                                                  $decoded = json_decode($detail->profile->religion, true);
                                                  $religionIds = is_array($decoded) ? $decoded : array_filter(explode(',', $detail->profile->religion));
                                              }
                                          }
                                          $religionLabels = [];
                                          foreach ($religionIds as $val) {
                                              if (is_numeric($val) && isset(getReligions()[$val])) {
                                                  $religionLabels[] = getReligions()[$val];
                                              } else {
                                                  $religionLabels[] = $val;
                                              }
                                          }
                                          $religionText = !empty($religionLabels) ? implode(', ', $religionLabels) : 'N/A';

                                          // Safely decode political_views
                                          $politicalIds = [];
                                          if (!empty($detail->profile?->political_views)) {
                                              if (is_array($detail->profile->political_views)) {
                                                  $politicalIds = $detail->profile->political_views;
                                              } else {
                                                  $decoded = json_decode($detail->profile->political_views, true);
                                                  $politicalIds = is_array($decoded) ? $decoded : array_filter(explode(',', $detail->profile->political_views));
                                              }
                                          }
                                          $politicalLabels = [];
                                          foreach ($politicalIds as $val) {
                                              if (is_numeric($val) && isset(getPoliticalViews()[$val])) {
                                                  $politicalLabels[] = getPoliticalViews()[$val];
                                              } else {
                                                  $politicalLabels[] = $val;
                                              }
                                          }
                                          $politicalText = !empty($politicalLabels) ? implode(', ', $politicalLabels) : 'N/A';

                                          // Safely decode music_tests
                                          $musicIds = [];
                                          if (!empty($detail->profile?->music_tests)) {
                                              if (is_array($detail->profile->music_tests)) {
                                                  $musicIds = $detail->profile->music_tests;
                                              } else {
                                                  $decoded = json_decode($detail->profile->music_tests, true);
                                                  $musicIds = is_array($decoded) ? $decoded : array_filter(explode(',', $detail->profile->music_tests));
                                              }
                                          }
                                          $musicLabels = [];
                                          foreach ($musicIds as $val) {
                                              if (is_numeric($val) && isset(getMusicTests()[$val])) {
                                                  $musicLabels[] = getMusicTests()[$val];
                                              } else {
                                                  $musicLabels[] = $val;
                                              }
                                          }
                                          $musicText = !empty($musicLabels) ? implode(', ', $musicLabels) : 'N/A';


                                         // Safely decode pets_current
                                         $petsCurrentIds = [];
                                         if (!empty($detail->profile?->pets_current)) {
                                             if (is_array($detail->profile->pets_current)) {
                                                 $petsCurrentIds = $detail->profile->pets_current;
                                             } else {
                                                 $decoded = json_decode($detail->profile->pets_current, true);
                                                 $petsCurrentIds = is_array($decoded) ? $decoded : array_filter(explode(',', $detail->profile->pets_current));
                                             }
                                         }
                                         $petsCurrentLabels = [];
                                         foreach ($petsCurrentIds as $id) {
                                             if (isset(getPetsCurrent()[$id])) {
                                                 $petsCurrentLabels[] = getPetsCurrent()[$id];
                                             }
                                         }
                                         $petsCurrentText = !empty($petsCurrentLabels) ? implode(', ', $petsCurrentLabels) : 'N/A';

                                         // Safely decode preferred_communication
                                         $preferredCommIds = [];
                                         if (!empty($detail->profile?->preferred_communication)) {
                                             if (is_array($detail->profile->preferred_communication)) {
                                                 $preferredCommIds = $detail->profile->preferred_communication;
                                             } else {
                                                 $decoded = json_decode($detail->profile->preferred_communication, true);
                                                 $preferredCommIds = is_array($decoded) ? $decoded : array_filter(explode(',', $detail->profile->preferred_communication));
                                             }
                                         }
                                         $preferredCommLabels = [];
                                         foreach ($preferredCommIds as $id) {
                                             if (isset(getPreferredCommunication()[$id])) {
                                                 $preferredCommLabels[] = getPreferredCommunication()[$id];
                                             }
                                         }
                                         $preferredCommText = !empty($preferredCommLabels) ? implode(', ', $preferredCommLabels) : 'N/A';

                                         // Safely decode love_language
                                         $loveLanguageIds = [];
                                         if (!empty($detail->profile?->love_language)) {
                                             if (is_array($detail->profile->love_language)) {
                                                 $loveLanguageIds = $detail->profile->love_language;
                                             } else {
                                                 $decoded = json_decode($detail->profile->love_language, true);
                                                 $loveLanguageIds = is_array($decoded) ? $decoded : array_filter(explode(',', $detail->profile->love_language));
                                             }
                                         }
                                         $loveLanguageLabels = [];
                                         foreach ($loveLanguageIds as $id) {
                                             if (isset(getLoveLanguage()[$id])) {
                                                 $loveLanguageLabels[] = getLoveLanguage()[$id];
                                             }
                                         }
                                         $loveLanguageText = !empty($loveLanguageLabels) ? implode(', ', $loveLanguageLabels) : 'N/A';

                                         // Safely decode languages_written
                                         $languagesWritten = [];
                                         if (!empty($detail->profile?->languages_written)) {
                                             if (is_array($detail->profile->languages_written)) {
                                                 $languagesWritten = $detail->profile->languages_written;
                                             } else {
                                                 $decoded = json_decode($detail->profile->languages_written, true);
                                                 $languagesWritten = is_array($decoded) ? $decoded : array_filter(explode(',', $detail->profile->languages_written));
                                             }
                                         }
                                         $languagesWrittenLabels = [];
                                         foreach ($languagesWritten as $val) {
                                             $languagesWrittenLabels[] = getLanguageName($val);
                                         }
                                         $languagesWrittenText = !empty($languagesWrittenLabels) ? implode(', ', $languagesWrittenLabels) : 'N/A';
                                     @endphp

                                     <!--begin::Overview Tab-->
                                     <div class="tab-pane fade show active" id="kt_user_view_overview_tab" role="tabpanel">

                                     <!--begin::Profile Card-->
                                     <div class="card card-flush mb-6 mb-xl-9 border-0 shadow-sm glass-card">
                                         <div class="card-header mt-6">
                                             <div class="card-title flex-column">
                                                 <h2 class="mb-1">Personal Details</h2>
                                                 <div class="fs-6 fw-semibold text-muted">Information from profile and account</div>
                                             </div>
                                         </div>
                                         <div class="card-body p-9 pt-4">
                                             <div class="row g-9 mb-7">
                                                 <div class="col-md-6">
                                                     <label class="fw-semibold text-muted d-block">Display Name</label>
                                                     <span class="fw-bold fs-6 text-gray-800">{{ $detail->profile?->display_name ?? 'N/A' }}</span>
                                                 </div>
                                                 <div class="col-md-6">
                                                     <label class="fw-semibold text-muted d-block">Date of Birth</label>
                                                     <span class="fw-bold fs-6 text-gray-800">{{ $detail->profile?->dob ? \Carbon\Carbon::parse($detail->profile->dob)->format('jS F Y') : 'N/A' }}</span>
                                                 </div>
                                                 <div class="col-md-6">
                                                     <label class="fw-semibold text-muted d-block">First Name</label>
                                                     <span class="fw-bold fs-6 text-gray-800">{{ $detail->profile?->first_name ?? 'N/A' }}</span>
                                                 </div>
                                                 <div class="col-md-6">
                                                     <label class="fw-semibold text-muted d-block">Last Name</label>
                                                     <span class="fw-bold fs-6 text-gray-800">{{ $detail->profile?->last_name ?? 'N/A' }}</span>
                                                 </div>
                                                 <div class="col-md-6">
                                                     <label class="fw-semibold text-muted d-block">Orientation</label>
                                                     <span class="fw-bold fs-6 text-gray-800">{{ getOrientation()[$detail->profile?->orientation] ?? 'N/A' }}</span>
                                                 </div>
                                                 <div class="col-md-6">
                                                     <label class="fw-semibold text-muted d-block">Dating Preferences</label>
                                                     <span class="fw-bold fs-6 text-gray-800">{{ $datingPrefText }}</span>
                                                 </div>
                                                 <div class="col-md-6">
                                                     <label class="fw-semibold text-muted d-block">Relationship Status</label>
                                                     <span class="fw-bold fs-6 text-gray-800">{{ getRelationshipStatus()[$detail->profile?->relationship_status] ?? 'N/A' }}</span>
                                                 </div>
                                                 <div class="col-md-3">
                                                     <label class="fw-semibold text-muted d-block">18+ Confirmed</label>
                                                     <span class="badge badge-{{ $detail->profile?->confirm_18_plus ?? 0 ? 'success' : 'light' }} fs-7 fw-bold px-3 py-2">
                                                         {{ $detail->profile?->confirm_18_plus ?? 0 ? 'Yes' : 'No' }}
                                                     </span>
                                                 </div>
                                                 <div class="col-md-3">
                                                     <label class="fw-semibold text-muted d-block">Agreed to Terms</label>
                                                     <span class="badge badge-{{ $detail->profile?->agreed_to_terms ?? 0 ? 'success' : 'light' }} fs-7 fw-bold px-3 py-2">
                                                         {{ $detail->profile?->agreed_to_terms ?? 0 ? 'Yes' : 'No' }}
                                                     </span>
                                                 </div>
                                             </div>

                                             <div class="row mb-7">
                                                 <div class="col-12">
                                                     <label class="fw-semibold text-muted d-block">About / Biography</label>
                                                     <div class="bg-light p-4 rounded text-gray-800 fs-6 border-start border-primary border-4">
                                                         {{ $detail->profile?->about ?? 'No biography provided.' }}
                                                     </div>
                                                 </div>
                                             </div>

                                             <div class="separator separator-dashed my-8"></div>
                                             <h3 class="card-title fw-bold mb-5"><i class="fas fa-map-marker-alt me-2 text-danger"></i> Contact & Location</h3>

                                             <div class="row g-9">
                                                 <div class="col-md-6">
                                                     <label class="fw-semibold text-muted d-block">Email Address</label>
                                                     <span class="fw-bold fs-6 text-gray-800">{{ $detail->email }}</span>
                                                 </div>
                                                 <div class="col-md-6">
                                                     <label class="fw-semibold text-muted d-block">Mobile Number</label>
                                                     <span class="fw-bold fs-6 text-gray-800">+{{ $detail->phone_code ?? '91' }} {{ $detail->mobile_number ?? 'N/A' }}</span>
                                                 </div>
                                                 <div class="col-md-4">
                                                     <label class="fw-semibold text-muted d-block">Country</label>
                                                     <span class="fw-bold fs-6 text-gray-800">{{ $detail->country_name }}</span>
                                                 </div>
                                                 <div class="col-md-4">
                                                     <label class="fw-semibold text-muted d-block">State</label>
                                                     <span class="fw-bold fs-6 text-gray-800">{{ $detail->state_name }}</span>
                                                 </div>
                                                 <div class="col-md-4">
                                                     <label class="fw-semibold text-muted d-block">City</label>
                                                     <span class="fw-bold fs-6 text-gray-800">{{ $detail->city_name }}</span>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                     <!--end::Profile Card-->

                                     <!--begin::Lifestyle & Home Cards-->
                                     <div class="row g-6 mb-6 mb-xl-9">
                                         <!-- Lifestyle Card -->
                                         <div class="col-md-6">
                                             <div class="card card-flush border-0 shadow-sm glass-card h-100">
                                                 <div class="card-header mt-6">
                                                     <h3 class="card-title align-items-start flex-column">
                                                         <span class="card-label fw-bold text-gray-800"><i class="fas fa-heartbeat me-2 text-success"></i>Lifestyle</span>
                                                         <span class="text-muted mt-1 fw-semibold fs-7">Daily habits and routines</span>
                                                     </h3>
                                                 </div>
                                                 <div class="card-body p-9 pt-4">
                                                     <div class="d-flex flex-stack mb-5">
                                                         <div class="fw-semibold text-muted me-2">Alcohol</div>
                                                         <div class="fw-bold text-gray-800">
                                                             {{ getAlcohol()[$detail->profile?->alcohol] ?? 'N/A' }}
                                                         </div>
                                                     </div>
                                                     <div class="d-flex flex-stack mb-5">
                                                         <div class="fw-semibold text-muted me-2">Smoking</div>
                                                         <div class="fw-bold text-gray-800">
                                                             {{ getSmoking()[$detail->profile?->smoking] ?? 'N/A' }}
                                                         </div>
                                                     </div>
                                                     <div class="d-flex flex-stack mb-5">
                                                         <div class="fw-semibold text-muted me-2">Work out / Exercise</div>
                                                         <div class="fw-bold text-gray-800">
                                                             {{ getExercise()[$detail->profile?->exercise] ?? 'N/A' }}
                                                         </div>
                                                     </div>
                                                     <div class="d-flex flex-stack mb-5">
                                                         <div class="fw-semibold text-muted me-2">Diet</div>
                                                         <div class="fw-bold text-gray-800">
                                                             {{ getDiet()[$detail->profile?->diet] ?? 'N/A' }}
                                                         </div>
                                                     </div>
                                                     <div class="d-flex flex-stack mb-0">
                                                         <div class="fw-semibold text-muted me-2">Sleep rhythm</div>
                                                         <div class="fw-bold text-gray-800">
                                                             {{ getSleepRhythm()[$detail->profile?->sleep_rhythm] ?? 'N/A' }}
                                                         </div>
                                                     </div>
                                                 </div>
                                             </div>
                                         </div>

                                         <!-- Home & Future Card -->
                                         <div class="col-md-6">
                                             <div class="card card-flush border-0 shadow-sm glass-card h-100">
                                                 <div class="card-header mt-6">
                                                     <h3 class="card-title align-items-start flex-column">
                                                         <span class="card-label fw-bold text-gray-800"><i class="fas fa-home me-2 text-warning"></i>Home & Future</span>
                                                         <span class="text-muted mt-1 fw-semibold fs-7">Family planning and preferences</span>
                                                     </h3>
                                                 </div>
                                                 <div class="card-body p-9 pt-4">
                                                     <div class="d-flex flex-stack mb-5">
                                                         <div class="fw-semibold text-muted me-2">Kids (have)</div>
                                                         <div class="fw-bold text-gray-800">
                                                             {{ getKidsHave()[$detail->profile?->kids_have] ?? 'N/A' }}
                                                         </div>
                                                     </div>
                                                     <div class="d-flex flex-stack mb-5">
                                                         <div class="fw-semibold text-muted me-2">Kids (future)</div>
                                                         <div class="fw-bold text-gray-800">
                                                             {{ getKidsFuture()[$detail->profile?->kids_future] ?? 'N/A' }}
                                                         </div>
                                                     </div>
                                                     <div class="d-flex flex-stack mb-5">
                                                         <div class="fw-semibold text-muted me-2">Pets (current)</div>
                                                         <div class="fw-bold text-gray-800 text-end">
                                                             {{ $petsCurrentText }}
                                                         </div>
                                                     </div>
                                                     <div class="d-flex flex-stack mb-5">
                                                         <div class="fw-semibold text-muted me-2">Pets (future)</div>
                                                         <div class="fw-bold text-gray-800">
                                                             {{ getPetsFuture()[$detail->profile?->pets_future] ?? 'N/A' }}
                                                         </div>
                                                     </div>
                                                     <div class="d-flex flex-stack mb-5">
                                                         <div class="fw-semibold text-muted me-2">Living preference</div>
                                                         <div class="fw-bold text-gray-800">
                                                             {{ getLivingPreference()[$detail->profile?->living_preference] ?? 'N/A' }}
                                                         </div>
                                                     </div>
                                                     <div class="d-flex flex-stack mb-0">
                                                         <div class="fw-semibold text-muted me-2">Travel importance</div>
                                                         <div class="fw-bold text-gray-800">
                                                             {{ getTravelImportance()[$detail->profile?->travel_importance] ?? 'N/A' }}
                                                         </div>
                                                     </div>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                     <!--end::Lifestyle & Home Cards-->

                                     <!--begin::Your Vibe Card-->
                                     <div class="card card-flush mb-6 mb-xl-9 border-0 shadow-sm glass-card">
                                         <div class="card-header mt-6">
                                             <div class="card-title flex-column">
                                                 <h2 class="mb-1"><i class="fas fa-smile me-2 text-primary"></i>Your Vibe</h2>
                                                 <div class="fs-6 fw-semibold text-muted">Personality, education and communication style</div>
                                             </div>
                                         </div>
                                         <div class="card-body p-9 pt-4">
                                             <div class="row g-9 mb-7">
                                                 <div class="col-md-6">
                                                     <label class="fw-semibold text-muted d-block">Preferred communication</label>
                                                     <span class="fw-bold fs-6 text-gray-800">{{ $preferredCommText }}</span>
                                                 </div>
                                                 <div class="col-md-6">
                                                     <label class="fw-semibold text-muted d-block">Love language</label>
                                                     <span class="fw-bold fs-6 text-gray-800">{{ $loveLanguageText }}</span>
                                                 </div>
                                                 <div class="col-md-4">
                                                     <label class="fw-semibold text-muted d-block">Social energy</label>
                                                     <span class="fw-bold fs-6 text-gray-800">
                                                         {{ getSocialEnergy()[$detail->profile?->social_energy] ?? 'N/A' }}
                                                     </span>
                                                 </div>
                                                 <div class="col-md-4">
                                                     <label class="fw-semibold text-muted d-block">MBTI Personality Type</label>
                                                     <span class="fw-bold fs-6 text-gray-800">
                                                         {{ getPersonalityType()[$detail->profile?->personality_type] ?? 'N/A' }}
                                                     </span>
                                                 </div>
                                                 <div class="col-md-4">
                                                     <label class="fw-semibold text-muted d-block">Education</label>
                                                     <span class="fw-bold fs-6 text-gray-800">
                                                         {{ getEducation()[$detail->profile?->education] ?? 'N/A' }}
                                                     </span>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                     <!--end::Your Vibe Card-->

                                     <!--begin::Dating & Profile Details Card-->
                                     <div class="card card-flush mb-6 mb-xl-9 border-0 shadow-sm glass-card">
                                         <div class="card-header mt-6">
                                             <div class="card-title flex-column">
                                                 <h2 class="mb-1"><i class="fas fa-venus-mars me-2 text-info"></i>Dating & Profile Details</h2>
                                                 <div class="fs-6 fw-semibold text-muted">Physical traits, dating criteria, and preferences</div>
                                             </div>
                                         </div>
                                         <div class="card-body p-9 pt-4">
                                             <div class="row g-9 mb-7">
                                                 <div class="col-md-6">
                                                     <label class="fw-semibold text-muted d-block">What I am Looking For</label>
                                                     <span class="fw-bold fs-6 text-gray-800">
                                                         {{ getWhatImLookingFor()[$detail->profile?->what_i_am_looking_for] ?? 'N/A' }}
                                                     </span>
                                                 </div>
                                                 <div class="col-md-6">
                                                     <label class="fw-semibold text-muted d-block">Private Album Enabled</label>
                                                     <span class="badge badge-{{ $detail->profile?->private_album ?? 0 ? 'success' : 'light' }} fs-7 fw-bold px-3 py-2">
                                                         {{ $detail->profile?->private_album ?? 0 ? 'Yes' : 'No' }}
                                                     </span>
                                                 </div>
                                                 
                                                  <div class="col-md-4">
                                                       <label class="fw-semibold text-muted d-block">Languages Spoken</label>
                                                       <span class="fw-bold fs-6 text-gray-800">{{ $languagesSpokenText }}</span>
                                                  </div>
                                                  <div class="col-md-4">
                                                       <label class="fw-semibold text-muted d-block">Languages Learning</label>
                                                       <span class="fw-bold fs-6 text-gray-800">{{ $languagesLearningText }}</span>
                                                  </div>
                                                  <div class="col-md-4">
                                                       <label class="fw-semibold text-muted d-block">Languages Written</label>
                                                       <span class="fw-bold fs-6 text-gray-800">{{ $languagesWrittenText }}</span>
                                                  </div>

                                                  <div class="col-md-3">
                                                      <label class="fw-semibold text-muted d-block">Height</label>
                                                      <span class="fw-bold fs-6 text-gray-800">
                                                          {{ $detail->profile?->height ? $detail->profile->height . ' cm' : 'N/A' }}
                                                      </span>
                                                  </div>
                                                  <div class="col-md-3">
                                                      <label class="fw-semibold text-muted d-block">Body Type</label>
                                                      <span class="fw-bold fs-6 text-gray-800">
                                                          {{ getBodyTypes()[$detail->profile?->body_type] ?? 'N/A' }}
                                                      </span>
                                                  </div>
                                                  <div class="col-md-3">
                                                      <label class="fw-semibold text-muted d-block">Eye Color</label>
                                                      <span class="fw-bold fs-6 text-gray-800">
                                                          {{ getEyeColors()[$detail->profile?->eye_color] ?? 'N/A' }}
                                                      </span>
                                                  </div>
                                                  <div class="col-md-3">
                                                      <label class="fw-semibold text-muted d-block">Hair Color</label>
                                                      <span class="fw-bold fs-6 text-gray-800">
                                                          {{ getHairColors()[$detail->profile?->hair_color] ?? 'N/A' }}
                                                      </span>
                                                  </div>
                                                  <div class="col-md-3">
                                                      <label class="fw-semibold text-muted d-block">Hair Length</label>
                                                      <span class="fw-bold fs-6 text-gray-800">
                                                          {{ getHairLengths()[$detail->profile?->hair_length] ?? 'N/A' }}
                                                      </span>
                                                  </div>
                                                  <div class="col-md-3">
                                                      <label class="fw-semibold text-muted d-block">Tattoos & Piercings</label>
                                                      <span class="fw-bold fs-6 text-gray-800">
                                                          {{ getTattoos()[$detail->profile?->tattoos] ?? 'N/A' }}
                                                      </span>
                                                  </div>
                                                  <div class="col-md-3">
                                                      <label class="fw-semibold text-muted d-block">Weight</label>
                                                      <span class="fw-bold fs-6 text-gray-800">
                                                          {{ $detail->profile?->weight ? $detail->profile->weight . ' kg' : 'N/A' }}
                                                      </span>
                                                  </div>
                                                  <div class="col-md-3">
                                                      <label class="fw-semibold text-muted d-block">Zodiac</label>
                                                      <span class="fw-bold fs-6 text-gray-800">
                                                          {{ getZodiacs()[$detail->profile?->zodiac] ?? 'N/A' }}
                                                      </span>
                                                  </div>

                                                  <div class="col-md-6">
                                                      <label class="fw-semibold text-muted d-block">Occupation</label>
                                                      <span class="fw-bold fs-6 text-gray-800">
                                                          {{ getOccupations()[$detail->profile?->occupation] ?? 'N/A' }}
                                                      </span>
                                                  </div>
                                                  <div class="col-md-6">
                                                      <label class="fw-semibold text-muted d-block">Specific Location</label>
                                                      <span class="fw-bold fs-6 text-gray-800">
                                                          {{ $detail->profile?->location ?? 'N/A' }}
                                                      </span>
                                                  </div>

                                                 <div class="col-md-3">
                                                     <label class="fw-semibold text-muted d-block">Sex Importance</label>
                                                     <span class="fw-bold fs-6 text-gray-800">
                                                         {{ getSexImportance()[$detail->profile?->sex_importance] ?? 'N/A' }}
                                                     </span>
                                                 </div>
                                                 <div class="col-md-3">
                                                     <label class="fw-semibold text-muted d-block">Role Position</label>
                                                     <span class="fw-bold fs-6 text-gray-800">
                                                         {{ getRolePositions()[$detail->profile?->role_position] ?? 'N/A' }}
                                                     </span>
                                                 </div>
                                                 <div class="col-md-3">
                                                     <label class="fw-semibold text-muted d-block">Dating Pace</label>
                                                     <span class="fw-bold fs-6 text-gray-800">
                                                         {{ getDatingPaces()[$detail->profile?->dating_pace] ?? 'N/A' }}
                                                     </span>
                                                 </div>
                                                 <div class="col-md-3">
                                                      <label class="fw-semibold text-muted d-block">Presentation Preference</label>
                                                      <span class="fw-bold fs-6 text-gray-800">
                                                          {{ getPresentationPreferences()[$detail->profile?->presentation_preference] ?? 'N/A' }}
                                                      </span>
                                                  </div>

                                                  <div class="col-md-3">
                                                      <label class="fw-semibold text-muted d-block">Nationality</label>
                                                      <span class="fw-bold fs-6 text-gray-800">
                                                          {{ $nationalityText }}
                                                      </span>
                                                  </div>
                                                  <div class="col-md-3">
                                                      <label class="fw-semibold text-muted d-block">Coming Out Status</label>
                                                      <div class="d-flex align-items-center">
                                                          <span class="fw-bold fs-6 text-gray-800">{{ $comingOutText }}</span>
                                                          <span class="badge badge-{{ $detail->profile?->show_coming_out_status ?? 1 ? 'light-success text-success' : 'light-danger text-danger' }} fs-9 fw-bold px-2 py-0.5 ms-2">
                                                              {{ $detail->profile?->show_coming_out_status ?? 1 ? 'Visible' : 'Hidden' }}
                                                          </span>
                                                      </div>
                                                  </div>

                                                  <div class="col-md-6">
                                                      <label class="fw-semibold text-muted d-block">Religion / Spirituality</label>
                                                      <div class="d-flex align-items-center">
                                                          <span class="fw-bold fs-6 text-gray-800">{{ $religionText }}</span>
                                                          <span class="badge badge-{{ $detail->profile?->show_religion ?? 1 ? 'light-success text-success' : 'light-danger text-danger' }} fs-9 fw-bold px-2 py-0.5 ms-2">
                                                              {{ $detail->profile?->show_religion ?? 1 ? 'Visible' : 'Hidden' }}
                                                          </span>
                                                      </div>
                                                  </div>
                                                  <div class="col-md-6">
                                                      <label class="fw-semibold text-muted d-block">Political Views</label>
                                                      <div class="d-flex align-items-center">
                                                          <span class="fw-bold fs-6 text-gray-800">{{ $politicalText }}</span>
                                                          <span class="badge badge-{{ $detail->profile?->show_political_views ?? 1 ? 'light-success text-success' : 'light-danger text-danger' }} fs-9 fw-bold px-2 py-0.5 ms-2">
                                                              {{ $detail->profile?->show_political_views ?? 1 ? 'Visible' : 'Hidden' }}
                                                          </span>
                                                      </div>
                                                  </div>
                                                  <div class="col-md-12">
                                                      <label class="fw-semibold text-muted d-block">Music Preference</label>
                                                      <span class="fw-bold fs-6 text-gray-800">{{ $musicText }}</span>
                                                  </div>

                                                 <div class="col-md-6">
                                                     <label class="fw-semibold text-muted d-block">Age Range Limit</label>
                                                     <span class="fw-bold fs-6 text-gray-800">
                                                         @if($detail->profile?->age_range_min || $detail->profile?->age_range_max)
                                                             {{ $detail->profile?->age_range_min ?? 'Any' }} - {{ $detail->profile?->age_range_max ?? 'Any' }} years
                                                         @else
                                                             N/A
                                                         @endif
                                                     </span>
                                                 </div>
                                                 <div class="col-md-6">
                                                     <label class="fw-semibold text-muted d-block">Location Details & Status</label>
                                                     <div class="d-flex flex-wrap gap-2 mt-1">
                                                         <span class="badge badge-light fw-bold text-gray-800">Height: {{ $detail->profile?->height ? $detail->profile->height . ' cm' : 'N/A' }}</span>
                                                         <span class="badge badge-light fw-bold text-gray-800">Living In: {{ $detail->profile?->living_in_city ?? 'N/A' }}, {{ $detail->profile?->living_in_country ?? 'N/A' }}</span>
                                                         <span class="badge badge-{{ $detail->profile?->currently_traveling ?? 0 ? 'warning text-white' : 'light text-gray-800' }} fw-bold">
                                                             {{ $detail->profile?->currently_traveling ?? 0 ? 'Currently Traveling' : 'Not Traveling' }}
                                                         </span>
                                                         <span class="badge badge-{{ $detail->profile?->show_location_on_profile ?? 1 ? 'success text-white' : 'light text-gray-800' }} fw-bold">
                                                             {{ $detail->profile?->show_location_on_profile ?? 1 ? 'Location Visible' : 'Location Hidden' }}
                                                         </span>
                                                     </div>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                     <!--end::Dating & Profile Details Card-->
                                 </div>
                                 <!--end::Overview Tab-->

                                <!--begin::Preferences & Safety Tab-->
                                <div class="tab-pane fade" id="kt_user_view_prefs_tab" role="tabpanel">
                                    <div class="row g-6 g-xl-9">
                                        <!--begin::Discovery Preferences-->
                                        <div class="col-md-6">
                                            <div class="card card-flush border-0 shadow-sm glass-card h-100">
                                                <div class="card-header pt-7">
                                                    <h3 class="card-title align-items-start flex-column">
                                                        <span class="card-label fw-bold text-gray-800">Discovery
                                                            Preferences</span>
                                                        <span class="text-muted mt-1 fw-semibold fs-7">How user finds
                                                            others</span>
                                                    </h3>
                                                </div>
                                                <div class="card-body pt-5">
                                                    <div class="d-flex flex-stack mb-5">
                                                        <div class="fw-semibold text-muted me-2">Age Range Recommendation
                                                        </div>
                                                        <div class="fw-bold text-gray-800">
                                                            {{ getAgeRanges()[$detail->profile?->age_range] ?? 'N/A' }}
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-stack mb-5">
                                                        <div class="fw-semibold text-muted me-2">Distance Range (km)</div>
                                                        <div class="fw-bold text-gray-800">
                                                            {{ getDistanceRanges()[$detail->profile?->distance_range] ?? 'N/A' }}
                                                        </div>
                                                    </div>
                                                    <div class="separator separator-dashed my-5"></div>
                                                    <div class="fw-bold text-gray-800 mb-3">Hobbies & Interests</div>
                                                    @forelse($detail->hobbies->groupBy(fn($item) => $item->hobby?->title ?? 'Other') as $category => $items)
                                                        <div class="mb-4">
                                                            <label class="fw-semibold text-muted fs-7 d-block mb-2">{{ $category }}</label>
                                                            <div class="d-flex flex-wrap">
                                                                @foreach($items as $item)
                                                                    <span class="badge badge-light-primary fs-7 fw-bold me-2 mb-2 px-3 py-2">
                                                                        <i class="fas fa-tag me-1 fs-8 text-primary"></i>{{ $item->name }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <span class="text-muted fs-7">No hobbies selected.</span>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                        <!--end::Discovery Preferences-->

                                        <!--begin::Looking For-->
                                        <div class="col-md-6">
                                            <div class="card card-flush border-0 shadow-sm glass-card h-100">
                                                <div class="card-header pt-7">
                                                    <h3 class="card-title align-items-start flex-column">
                                                        <span class="card-label fw-bold text-gray-800">Looking For</span>
                                                        <span class="text-muted mt-1 fw-semibold fs-7">User goals and
                                                            intentions</span>
                                                    </h3>
                                                </div>
                                                <div class="card-body pt-5">
                                                    <div class="d-flex align-items-center mb-4">
                                                        <div class="symbol symbol-40px me-3">
                                                            <div
                                                                class="symbol-label bg-light-{{ $detail->profile?->friends ?? 0 ? 'success' : 'light' }}">
                                                                <i
                                                                    class="fas fa-user-friends text-{{ $detail->profile?->friends ?? 0 ? 'success' : 'gray-400' }}"></i>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <span class="text-gray-800 fw-bold">Friendship</span>
                                                            <span
                                                                class="text-muted fw-semibold fs-7">{{ $detail->profile?->friends ?? 0 ? 'Interested' : 'Not Interested' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center mb-4">
                                                        <div class="symbol symbol-40px me-3">
                                                            <div
                                                                class="symbol-label bg-light-{{ $detail->profile?->dates ?? 0 ? 'success' : 'light' }}">
                                                                <i
                                                                    class="fas fa-heart text-{{ $detail->profile?->dates ?? 0 ? 'success' : 'gray-400' }}"></i>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <span class="text-gray-800 fw-bold">Dating</span>
                                                            <span
                                                                class="text-muted fw-semibold fs-7">{{ $detail->profile?->dates ?? 0 ? 'Interested' : 'Not Interested' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center mb-4">
                                                        <div class="symbol symbol-40px me-3">
                                                            <div
                                                                class="symbol-label bg-light-{{ $detail->profile?->hookups ?? 0 ? 'success' : 'light' }}">
                                                                <i
                                                                    class="fas fa-fire text-{{ $detail->profile?->hookups ?? 0 ? 'success' : 'gray-400' }}"></i>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <span class="text-gray-800 fw-bold">Hookups</span>
                                                            <span
                                                                class="text-muted fw-semibold fs-7">{{ $detail->profile?->hookups ?? 0 ? 'Interested' : 'Not Interested' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-40px me-3">
                                                            <div
                                                                class="symbol-label bg-light-{{ $detail->profile?->events_and_communities ?? 0 ? 'success' : 'light' }}">
                                                                <i
                                                                    class="fas fa-users text-{{ $detail->profile?->events_and_communities ?? 0 ? 'success' : 'gray-400' }}"></i>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <span class="text-gray-800 fw-bold">Events & Communities</span>
                                                            <span
                                                                class="text-muted fw-semibold fs-7">{{ $detail->profile?->events_and_communities ?? 0 ? 'Interested' : 'Not Interested' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--end::Looking For-->

                                        <!--begin::Privacy & Visibility-->
                                        <div class="col-12">
                                            <div class="card card-flush border-0 shadow-sm glass-card">
                                                <div class="card-header pt-7">
                                                    <h3 class="card-title align-items-start flex-column">
                                                        <span class="card-label fw-bold text-gray-800">Safety, Privacy &
                                                            Visibility</span>
                                                        <span class="text-muted mt-1 fw-semibold fs-7">Control over account
                                                            presence and data</span>
                                                    </h3>
                                                </div>
                                                <div class="card-body pt-5">
                                                    <div class="row g-9">
                                                        <div class="col-md-4">
                                                            <h4 class="fw-bold mb-4 fs-6 text-primary">Safety Settings</h4>
                                                            <div class="d-flex flex-stack mb-3">
                                                                <span class="fw-semibold text-gray-600">Guest Mode</span>
                                                                <span
                                                                    class="badge badge-{{ $detail->profile?->guest_mode ?? 0 ? 'success' : 'light' }}">{{ $detail->profile?->guest_mode ?? 0 ? 'ON' : 'OFF' }}</span>
                                                            </div>
                                                            <div class="d-flex flex-stack mb-3">
                                                                <span class="fw-semibold text-gray-600">Verified Profiles
                                                                    Only</span>
                                                                <span
                                                                    class="badge badge-{{ $detail->profile?->verified_profiles ?? 0 ? 'success' : 'light' }}">{{ $detail->profile?->verified_profiles ?? 0 ? 'ON' : 'OFF' }}</span>
                                                            </div>
                                                            <div class="d-flex flex-stack mb-3">
                                                                <span class="fw-semibold text-gray-600">Invite Only
                                                                    Access</span>
                                                                <span
                                                                    class="badge badge-{{ $detail->profile?->invite_only_access ?? 0 ? 'success' : 'light' }}">{{ $detail->profile?->invite_only_access ?? 0 ? 'ON' : 'OFF' }}</span>
                                                            </div>
                                                            <div class="d-flex flex-stack">
                                                                <span class="fw-semibold text-gray-600">Disable
                                                                    Tracking</span>
                                                                <span
                                                                    class="badge badge-{{ $detail->profile?->no_tracking ?? 0 ? 'success' : 'light' }}">{{ $detail->profile?->no_tracking ?? 0 ? 'ON' : 'OFF' }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-8 border-start border-gray-200 ps-lg-15">
                                                            <h4 class="fw-bold mb-4 fs-6 text-primary">Visibility Control
                                                            </h4>
                                                            <p class="text-muted fs-7 mb-5">Determines who can see the
                                                                user's profile on the platform.</p>

                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div
                                                                        class="border border-gray-300 border-{{ $detail->profile?->everyone ?? 0 ? 'primary' : 'dashed' }} rounded p-4 text-center {{ $detail->profile?->everyone ?? 0 ? 'bg-light-primary' : '' }}">
                                                                        <i
                                                                            class="fas fa-globe fs-2 mb-2 {{ $detail->profile?->everyone ?? 0 ? 'text-primary' : 'text-gray-400' }}"></i>
                                                                        <div class="fw-bold text-gray-800">Everyone</div>
                                                                        <div
                                                                            class="text-{{ $detail->profile?->everyone ?? 0 ? 'primary' : 'muted' }} fs-7">
                                                                            {{ $detail->profile?->everyone ?? 0 ? 'Active' : 'Inactive' }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div
                                                                        class="border border-gray-300 border-{{ $detail->profile?->selected_groups ?? 0 ? 'info' : 'dashed' }} rounded p-4 text-center {{ $detail->profile?->selected_groups ?? 0 ? 'bg-light-info' : '' }}">
                                                                        <i
                                                                            class="fas fa-users-cog fs-2 mb-2 {{ $detail->profile?->selected_groups ?? 0 ? 'text-info' : 'text-gray-400' }}"></i>
                                                                        <div class="fw-bold text-gray-800">Selected Groups
                                                                        </div>
                                                                        <div
                                                                            class="text-{{ $detail->profile?->selected_groups ?? 0 ? 'info' : 'muted' }} fs-7">
                                                                            {{ $detail->profile?->selected_groups ?? 0 ? 'Active' : 'Inactive' }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div
                                                                        class="border border-gray-300 border-{{ $detail->profile?->no_one_at_all ?? 0 ? 'danger' : 'dashed' }} rounded p-4 text-center {{ $detail->profile?->no_one_at_all ?? 0 ? 'bg-light-danger' : '' }}">
                                                                        <i
                                                                            class="fas fa-user-slash fs-2 mb-2 {{ $detail->profile?->no_one_at_all ?? 0 ? 'text-danger' : 'text-gray-400' }}"></i>
                                                                        <div class="fw-bold text-gray-800">No One</div>
                                                                        <div
                                                                            class="text-{{ $detail->profile?->no_one_at_all ?? 0 ? 'danger' : 'muted' }} fs-7">
                                                                            {{ $detail->profile?->no_one_at_all ?? 0 ? 'Active' : 'Inactive' }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--end::Privacy & Visibility-->
                                    </div>
                                </div>
                                <!--end::Preferences & Safety Tab-->

                                <!--begin::Gallery Tab-->
                                <div class="tab-pane fade" id="kt_user_view_gallery_tab" role="tabpanel">
                                    <div class="card card-flush mb-6 mb-xl-9 border-0 shadow-sm glass-card">
                                        <div class="card-header mt-6">
                                            <div class="card-title flex-column">
                                                <h2 class="mb-1">Photo Gallery</h2>
                                                <div class="fs-6 fw-semibold text-muted">{{ $detail->galleries->count() }}
                                                    total images uploaded by user</div>
                                            </div>
                                        </div>
                                        <div class="card-body p-9 pt-4">
                                            @if ($detail->galleries->isNotEmpty())
                                                <div class="row g-6">
                                                    @foreach ($detail->galleries as $gallery)
                                                        <div class="col-md-4 col-sm-6">
                                                            <div
                                                                class="card card-flush shadow-sm overlay overflow-hidden rounded-3">
                                                                <div class="overlay-wrapper">
                                                                    <img src="{{ $gallery->image_path }}"
                                                                        alt="Gallery Image"
                                                                        class="w-100 h-250px object-fit-cover shadow-sm transition-transform duration-500 hover-scale"
                                                                        style="object-position: center;">
                                                                </div>
                                                                <div
                                                                    class="overlay-layer bg-dark bg-opacity-25 align-items-end justify-content-center p-5">
                                                                    <a target="_blank" href="{{ $gallery->image_path }}"
                                                                        data-fslightbox="gallery"
                                                                        class="btn btn-primary btn-sm fw-bold">
                                                                        <i class="fas fa-search-plus me-1"></i> View Full
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center py-20">
                                                    <i class="fas fa-images fs-3x text-gray-300 mb-5"></i>
                                                    <div class="text-gray-600 fs-4 fw-bold">No images found</div>
                                                    <div class="text-muted fs-6">User hasn't uploaded any photos to their
                                                        gallery yet.</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <!--end::Gallery Tab-->

                                <!--begin::Friends Tab-->
                                <div class="tab-pane fade" id="kt_user_view_friends_tab" role="tabpanel">
                                    <div class="card card-flush mb-6 mb-xl-9 border-0 shadow-sm glass-card">
                                        <div class="card-header mt-6">
                                            <div class="card-title flex-column">
                                                <h2 class="mb-1">Friends</h2>
                                                <div class="fs-6 fw-semibold text-muted">{{ $friends->count() }} total friends</div>
                                            </div>
                                        </div>
                                        <div class="card-body p-9 pt-4">
                                            @if ($friends->isNotEmpty())
                                                <div class="row g-6">
                                                    @foreach ($friends as $friend)
                                                        <div class="col-md-4 col-sm-6">
                                                            <div class="card card-flush shadow-sm h-100 rounded-3">
                                                                <div class="card-body d-flex flex-center flex-column p-9">
                                                                    <div class="symbol symbol-65px symbol-circle mb-5">
                                                                        <img src="{{ $friend->image_path }}" alt="image" />
                                                                    </div>
                                                                    <a href="{{ route('admin.user.view', $friend->uuid) }}" class="fs-4 text-gray-800 text-hover-primary fw-bold mb-0">{{ $friend->name }}</a>
                                                                    <div class="fw-semibold text-gray-400 mb-6">{{ $friend->email }}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center py-20">
                                                    <i class="fas fa-user-friends fs-3x text-gray-300 mb-5"></i>
                                                    <div class="text-gray-600 fs-4 fw-bold">No friends found</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <!--end::Friends Tab-->

                                <!--begin::Blocked Tab-->
                                <div class="tab-pane fade" id="kt_user_view_blocked_tab" role="tabpanel">
                                    <div class="card card-flush mb-6 mb-xl-9 border-0 shadow-sm glass-card">
                                        <div class="card-header mt-6">
                                            <div class="card-title flex-column">
                                                <h2 class="mb-1">Blocked Users</h2>
                                                <div class="fs-6 fw-semibold text-muted">{{ $blockedUsers->count() }} total blocked users</div>
                                            </div>
                                        </div>
                                        <div class="card-body p-9 pt-4">
                                            @if ($blockedUsers->isNotEmpty())
                                                <div class="row g-6">
                                                    @foreach ($blockedUsers as $blockedUser)
                                                        <div class="col-md-4 col-sm-6">
                                                            <div class="card card-flush shadow-sm h-100 rounded-3">
                                                                <div class="card-body d-flex flex-center flex-column p-9">
                                                                    <div class="symbol symbol-65px symbol-circle mb-5">
                                                                        <img src="{{ $blockedUser->image_path }}" alt="image" />
                                                                    </div>
                                                                    <a href="{{ route('admin.user.view', $blockedUser->uuid) }}" class="fs-4 text-gray-800 text-hover-primary fw-bold mb-0">{{ $blockedUser->name }}</a>
                                                                    <div class="fw-semibold text-gray-400 mb-6">{{ $blockedUser->email }}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center py-20">
                                                    <i class="fas fa-user-lock fs-3x text-gray-300 mb-5"></i>
                                                    <div class="text-gray-600 fs-4 fw-bold">No blocked users found</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <!--end::Blocked Tab-->
                            </div>
                        </div>
                        <!--end::Main Content-->
                    </div>
                </div>
            </div>
            <!--end::Content-->
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.7) !important;
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
        }

        .hover-scale:hover {
            transform: scale(1.05);
        }

        .transition-transform {
            transition: transform 0.3s ease-in-out;
        }
    </style>
@endpush

@push('script')
    <script>
        $(document).on('dblclick', '.copy-original-password', function() {
            var password = $(this).data('password');
            if (!password) return;

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(password).catch(function() {
                    fallbackCopy(password);
                });
            } else {
                fallbackCopy(password);
            }

            function fallbackCopy(text) {
                var $temp = $("<input>");
                $("body").append($temp);
                $temp.val(text).select();
                document.execCommand("copy");
                $temp.remove();
            }
        });
    </script>
@endpush
