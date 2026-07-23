@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!-- Toolbar -->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Live Chat</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Live Chat</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">

                    <div class="d-flex flex-column flex-lg-row">
                        <!-- Sidebar: Contact List -->
                        <div class="flex-column flex-lg-row-auto w-100 w-lg-300px w-xl-400px mb-10 mb-lg-0">
                            <div class="card card-flush glass-card">
                                <div class="card-header pt-7" id="kt_chat_contacts_header">
                                    <form class="w-100 position-relative" autocomplete="off">
                                        <i
                                            class="fas fa-search position-absolute top-50 translate-middle-y ms-4 text-gray-500"></i>
                                        <input type="text" class="form-control form-control-solid px-12 glass-input"
                                            name="search" placeholder="Search by name or email...">
                                    </form>
                                </div>
                                <div class="card-body pt-5" id="kt_chat_contacts_body"
                                    style="padding: 1.5rem 1rem !important;">
                                    <ul class="nav nav-pills nav-pills-custom mb-4 border-bottom border-light pb-2"
                                        style="justify-content: center;">
                                        <li class="nav-item">
                                            <a class="nav-link active btn btn-sm btn-color-muted btn-active-primary px-4 py-2 me-2"
                                                data-bs-toggle="tab" href="#kt_chat_recent"
                                                style="border-radius: 20px;">Recent</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link btn btn-sm btn-color-muted btn-active-primary px-4 py-2 me-2"
                                                data-bs-toggle="tab" href="#kt_chat_groups"
                                                style="border-radius: 20px;">Groups</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link btn btn-sm btn-color-muted btn-active-primary px-4 py-2"
                                                data-bs-toggle="tab" href="#kt_chat_users" style="border-radius: 20px;">All
                                                Users</a>
                                        </li>
                                    </ul>

                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="kt_chat_recent" role="tabpanel">
                                            <div class="scroll-y pe-2 h-300px h-lg-auto kt-scroll-content"
                                                data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                                                data-kt-scroll-max-height="auto"
                                                data-kt-scroll-dependencies="#kt_header, #kt_toolbar, #kt_footer, #kt_chat_contacts_header"
                                                data-kt-scroll-wrappers="#kt_content, #kt_chat_contacts_body"
                                                data-kt-scroll-offset="0px" style="max-height: 500px;">

                                                <!-- Contacts List -->
                                                @foreach ($chats->where('is_group', false) as $chat)
                                                    @php
                                                        $otherUser = $chat->users->firstWhere('id', '!=', auth()->id());
                                                        $chatName = $otherUser ? $otherUser->name : 'Unknown User';
                                                        $chatImage = $otherUser
                                                            ? $otherUser->image_path
                                                            : asset('assets/media/svg/avatars/blank.svg');
                                                    @endphp
                                                    <div class="d-flex flex-stack py-3 px-3 chat-contact-item mb-2"
                                                        data-chat-id="{{ $chat->id }}"
                                                        data-chat-name="{{ $chatName }}" data-is-group="false"
                                                        style="cursor: pointer;">
                                                        <div class="d-flex align-items-center">
                                                            <div class="symbol symbol-40px symbol-circle">
                                                                <img alt="Pic" src="{{ $chatImage }}">
                                                                <div
                                                                    class="symbol-badge {{ ($otherUser && $otherUser->is_online) ? 'bg-success' : 'bg-secondary' }} start-100 top-100 border-4 h-10px w-10px ms-n2 mt-n2" title="{{ ($otherUser && $otherUser->is_online) ? 'Online' : 'Offline' }}">
                                                                </div>
                                                            </div>
                                                            <div class="ms-4">
                                                                <a href="javascript:void(0)"
                                                                    class="fs-6 fw-bold text-gray-900 text-hover-primary mb-1 chat-name-label d-block">{{ $chatName }}</a>
                                                                <div class="fw-semibold text-muted fs-8">
                                                                    {{ $chat->latestMessage ? Str::limit($chat->latestMessage->message, 25) : 'No messages yet' }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-column align-items-end ms-2">
                                                            <span
                                                                class="text-muted fs-8 mb-1">{{ $chat->latestMessage ? $chat->latestMessage->created_at->format('H:i') : '' }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="kt_chat_groups" role="tabpanel">
                                            <div class="scroll-y pe-2 h-300px h-lg-auto kt-scroll-content"
                                                data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                                                data-kt-scroll-max-height="auto"
                                                data-kt-scroll-dependencies="#kt_header, #kt_toolbar, #kt_footer, #kt_chat_contacts_header"
                                                data-kt-scroll-wrappers="#kt_content, #kt_chat_contacts_body"
                                                data-kt-scroll-offset="0px" style="max-height: 500px;">

                                                <!-- Groups List -->
                                                @foreach ($chats->where('is_group', true) as $chat)
                                                    <div class="d-flex flex-stack py-3 px-3 chat-contact-item mb-2"
                                                        data-chat-id="{{ $chat->id }}"
                                                        data-chat-name="{{ $chat->name }}" data-is-group="true"
                                                        style="cursor: pointer;">
                                                        <div class="d-flex align-items-center">
                                                            <div class="symbol symbol-40px symbol-circle">
                                                                <img alt="Pic" src="{{ $chat->image_path }}">
                                                            </div>
                                                            <div class="ms-4">
                                                                <a href="javascript:void(0)"
                                                                    class="fs-6 fw-bold text-gray-900 text-hover-primary mb-1 chat-name-label d-block">{{ $chat->name }}</a>
                                                                <div class="fw-semibold text-muted fs-8">
                                                                    {{ $chat->latestMessage ? Str::limit($chat->latestMessage->message, 25) : 'No messages yet' }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-column align-items-end ms-2">
                                                            <span
                                                                class="text-muted fs-8 mb-1">{{ $chat->latestMessage ? $chat->latestMessage->created_at->format('H:i') : '' }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="kt_chat_users" role="tabpanel">
                                            <div class="scroll-y pe-2 h-300px h-lg-auto kt-scroll-content"
                                                data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                                                data-kt-scroll-max-height="auto"
                                                data-kt-scroll-dependencies="#kt_header, #kt_toolbar, #kt_footer, #kt_chat_contacts_header"
                                                data-kt-scroll-wrappers="#kt_content, #kt_chat_contacts_body"
                                                data-kt-scroll-offset="0px" style="max-height: 500px;">

                                                <!-- Users List -->
                                                @foreach ($users as $user)
                                                    <div class="d-flex flex-stack py-3 px-3 user-contact-item chat-contact-item mb-2"
                                                        data-user-id="{{ $user->id }}"
                                                        data-user-name="{{ $user->name }}" style="cursor: pointer;">
                                                        <div class="d-flex align-items-center">
                                                            <div class="symbol symbol-40px symbol-circle">
                                                                <img alt="Pic" src="{{ $user->image_path }}">
                                                                <div class="symbol-badge {{ $user->is_online ? 'bg-success' : 'bg-secondary' }} start-100 top-100 border-4 h-10px w-10px ms-n2 mt-n2" title="{{ $user->is_online ? 'Online' : 'Offline' }}"></div>
                                                            </div>
                                                            <div class="ms-4">
                                                                <a href="javascript:void(0)"
                                                                    class="fs-6 fw-bold text-gray-900 text-hover-primary mb-1 chat-name-label d-block">{{ $user->name }}</a>
                                                                <div class="fw-semibold text-muted fs-8">
                                                                    {{ Str::limit($user->email, 20) }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-column align-items-end ms-2">
                                                            <span
                                                                class="badge badge-light-primary fw-bold text-muted fs-9 px-2 py-1"
                                                                style="border-radius: 6px;">Start</span>
                                                        </div>
                                                    </div>
                                                @endforeach

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Chat Screen -->
                        <div class="flex-lg-row-fluid ms-lg-7 ms-xl-10">
                            <div class="card glass-card shadow-sm h-100 d-flex flex-column" id="kt_chat_messenger"
                                style="border-radius: 20px; overflow: hidden;">
                                <div class="card-header border-bottom-1 border-light py-4" id="kt_chat_messenger_header"
                                    style="background: rgba(255, 255, 255, 0.45);">
                                    <div class="card-title w-100 d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-45px symbol-circle me-4 d-none"
                                                id="active-chat-avatar-container">
                                                <img alt="Pic"
                                                    src="{{ asset('assets/media/svg/avatars/001-boy.svg') }}"
                                                    id="active-chat-avatar">
                                                <div
                                                    class="symbol-badge bg-success start-100 top-100 border-4 h-12px w-12px ms-n2 mt-n2">
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-center flex-column">
                                                <a href="javascript:void(0)"
                                                    class="fs-4 fw-bolder text-gray-900 text-hover-primary mb-1 lh-1"
                                                    id="active-chat-name">Select a conversation...</a>
                                                <div class="mb-0 lh-1 d-none" id="active-chat-status">
                                                    <span class="fs-7 fw-medium text-muted">Online</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="d-flex align-items-center me-n2 d-none" id="chat-header-actions">
                                            <button class="btn btn-sm btn-icon btn-active-light-primary"
                                                id="toggle-info-btn" data-bs-toggle="tooltip" title="Chat Info">
                                                <i class="fas fa-info-circle fs-3 text-primary"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-0 d-flex flex-column" id="kt_chat_messenger_body"
                                    style="background: rgba(250, 250, 255, 0.3);">
                                    <div class="scroll-y p-5 p-lg-10 h-300px h-lg-auto chat-messages-container flex-grow-1"
                                        id="messages-scroll-area" data-kt-element="messages" data-kt-scroll="true"
                                        data-kt-scroll-activate="{default: false, lg: true}"
                                        data-kt-scroll-max-height="auto"
                                        data-kt-scroll-dependencies="#kt_header, #kt_toolbar, #kt_footer, #kt_chat_messenger_header, #kt_chat_messenger_footer"
                                        data-kt-scroll-wrappers="#kt_content, #kt_chat_messenger_body"
                                        data-kt-scroll-offset="5px" style="max-height: 500px; scroll-behavior: smooth;">

                                        <!-- Messages will be appended here -->
                                        <div class="d-flex justify-content-center align-items-center h-100">
                                            <div class="text-center text-muted px-6 py-10 rounded-3"
                                                style="background: rgba(255,255,255,0.6);">
                                                <i class="fas fa-comment-dots fs-3x mb-4 text-gray-300"></i>
                                                <div class="fs-5 fw-medium">Pick a chat from the left panel to say Hi! 👋
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="card-footer pt-4 pb-4 px-6" id="kt_chat_messenger_footer"
                                    style="display: none; background: rgba(255, 255, 255, 0.6); border-top: 1px solid rgba(255,255,255,0.8);">
                                    <form id="chat-form" enctype="multipart/form-data">

                                        <!-- Reply Preview Bar -->
                                        <div id="reply-preview-bar"
                                            class="d-none mb-3 p-2 rounded-3 bg-light-primary border-start border-4 border-primary position-relative d-flex justify-content-between align-items-center shadow-sm">
                                            <div class="pe-3">
                                                <div class="fw-bold fs-7 text-primary mb-0" id="reply-preview-name">Replying...</div>
                                                <div class="text-muted fs-8 text-truncate" id="reply-preview-text" style="max-width: 320px;"></div>
                                            </div>
                                            <button type="button"
                                                class="btn btn-icon btn-sm btn-active-light-danger rounded-circle"
                                                id="cancel-reply-btn" title="Cancel Reply"
                                                style="width: 26px; height: 26px;">
                                                <i class="fas fa-times fs-7"></i>
                                            </button>
                                        </div>

                                        <!-- Preview Area -->
                                        <div id="attachment-preview"
                                            class="d-none mb-3 position-relative p-2 rounded-3 bg-white shadow-sm"
                                            style="max-width: fit-content; border: 1px solid rgba(0,0,0,0.05);">
                                            <img src="" id="img-preview" class="img-thumbnail border-0 rounded"
                                                style="max-height: 120px; display: none;">
                                            <video src="" id="vid-preview" class="rounded"
                                                style="max-height: 120px; display: none;" controls></video>
                                            <audio src="" id="aud-preview" style="display: none; width: 250px;"
                                                controls></audio>
                                            <button type="button"
                                                class="btn btn-icon btn-sm btn-active-light-danger position-absolute top-0 end-0 translate-middle"
                                                id="clear-attachment"
                                                style="background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-radius: 50%; width: 25px; height: 25px; min-width: 25px;">
                                                <i class="fas fa-times fs-8"></i>
                                            </button>
                                        </div>

                                        <div class="position-relative d-flex align-items-center">
                                            <div class="d-flex align-items-center me-3 chat-action-buttons">
                                                <!-- Image Upload -->
                                                <button
                                                    class="btn btn-sm btn-icon btn-color-gray-500 btn-active-color-primary me-2 shadow-sm bg-white"
                                                    type="button" data-bs-toggle="tooltip" title="Send Image"
                                                    onclick="document.getElementById('image-upload').click()"
                                                    style="border-radius: 12px; height: 40px; width: 40px;">
                                                    <i class="fas fa-image fs-4"></i>
                                                </button>
                                                <input type="file" id="image-upload" accept="image/*" class="d-none">

                                                <!-- Video Upload -->
                                                <button
                                                    class="btn btn-sm btn-icon btn-color-gray-500 btn-active-color-primary me-2 shadow-sm bg-white"
                                                    type="button" data-bs-toggle="tooltip" title="Send Video"
                                                    onclick="document.getElementById('video-upload').click()"
                                                    style="border-radius: 12px; height: 40px; width: 40px;">
                                                    <i class="fas fa-video fs-4"></i>
                                                </button>
                                                <input type="file" id="video-upload" accept="video/*" class="d-none">

                                                <!-- Audio Record -->
                                                <button
                                                    class="btn btn-sm btn-icon btn-color-gray-500 btn-active-color-danger shadow-sm bg-white"
                                                    type="button" id="record-audio-btn" data-bs-toggle="tooltip"
                                                    title="Record Voice"
                                                    style="border-radius: 12px; height: 40px; width: 40px;">
                                                    <i class="fas fa-microphone fs-4"></i>
                                                </button>
                                            </div>
                                            <div class="flex-grow-1 chat-input-wrapper">
                                                <textarea class="form-control chat-textarea" rows="1" data-kt-element="input"
                                                    placeholder="Type a message..." id="message-input"></textarea>

                                                <button
                                                    class="btn btn-primary btn-icon glass-button shadow-sm d-flex justify-content-center align-items-center p-0 send-btn"
                                                    type="submit" data-kt-element="send"
                                                    style="height: 36px; width: 36px; min-width: 36px; border-radius: 50%;">
                                                    <i class="fas fa-paper-plane fs-5 m-0 position-relative"
                                                        style="right: 1px; color: #ffffff !important;"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div id="recording-indicator"
                                            class="text-danger mt-3 d-none fw-semibold fs-7 text-center">
                                            <i class="fas fa-circle rounded-circle fa-fade me-2"></i> Recording Voice
                                            Note... <span id="recording-time" class="ms-1 fw-bold">0:00</span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Info Sidebar (Media/Details) -->
                        <div class="flex-column flex-lg-row-auto w-100 w-lg-300px w-xl-350px ms-lg-7 ms-xl-10 d-none"
                            id="chat-info-sidebar">
                            <div class="card card-flush glass-card h-100">
                                <div class="card-header pt-7">
                                    <div class="card-title">
                                        <h2 class="fs-4 fw-bold text-gray-900">Chat Info</h2>
                                    </div>
                                    <div class="card-toolbar">
                                        <button class="btn btn-sm btn-icon btn-active-light-danger" id="close-info-btn">
                                            <i class="fas fa-times fs-3"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body pt-5">
                                    <div class="d-flex flex-column align-items-center mb-8">
                                        <div class="symbol symbol-100px symbol-circle mb-5 border border-2 border-primary">
                                            <img src="{{ asset('assets/media/svg/avatars/001-boy.svg') }}"
                                                id="info-chat-avatar" alt="image">
                                        </div>
                                        <div class="fs-4 fw-bolder text-gray-900 mx-3 text-center mb-1"
                                            id="info-chat-name">Name</div>
                                    </div>

                                    <div class="fs-5 fw-bold text-gray-800 border-bottom pb-3 mb-5"
                                        id="members-section-title" style="display: none;">Group Members</div>
                                    <div class="scroll-y pe-2 mb-8" id="members-list-container"
                                        style="max-height: 250px; display: none;">
                                        <!-- Members will be appended here -->
                                    </div>

                                    <div class="fs-5 fw-bold text-gray-800 border-bottom pb-3 mb-5">Shared Media</div>

                                    <div class="scroll-y pe-2 kt-scroll-content h-300px" data-kt-scroll="true"
                                        data-kt-scroll-activate="{default: false, lg: true}"
                                        data-kt-scroll-max-height="auto"
                                        data-kt-scroll-dependencies="#kt_header, #kt_toolbar"
                                        data-kt-scroll-wrappers="#chat-info-sidebar" data-kt-scroll-offset="0px"
                                        style="max-height: 400px;">

                                        <!-- Shared Media Grid -->
                                        <div class="row g-2" id="shared-media-grid">
                                            <div class="text-center text-muted py-5 w-100" id="no-media-msg">No media
                                                shared yet.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" tabindex="-1" id="chat_image_modal" style="z-index: 99999;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0 shadow-none">
                <div class="modal-header border-0 justify-content-end p-0 mb-2">
                    <button type="button" class="btn btn-icon btn-sm btn-color-white btn-active-color-primary"
                        data-bs-dismiss="modal">
                        <i class="fas fa-times fs-2 shadow-sm text-white"></i>
                    </button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="" id="chat_modal_image" class="img-fluid rounded shadow-lg"
                        style="max-height: 80vh;" alt="Viewed Image">
                </div>
            </div>
        </div>
    </div>

    <!-- Forward Message Modal -->
    <div class="modal fade" tabindex="-1" id="chat_forward_modal" style="z-index: 99999;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card p-3">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-gray-900"><i class="fas fa-share text-success me-2"></i> Forward Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <input type="hidden" id="forward-message-id">
                    <label class="form-label fs-7 fw-semibold text-gray-700 mb-2">Select conversation(s) to forward to:</label>
                    <div class="scroll-y pe-2" id="forward-chat-list" style="max-height: 250px;">
                        <!-- Dynamically populated chats -->
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" id="confirm-forward-btn"><i class="fas fa-paper-plane me-1"></i> Forward</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Message Modal -->
    <div class="modal fade" tabindex="-1" id="chat_edit_modal" style="z-index: 99999;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card p-3">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-gray-900"><i class="fas fa-edit text-info me-2"></i> Edit Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <input type="hidden" id="edit-message-id">
                    <textarea class="form-control" id="edit-message-text" rows="3" placeholder="Type updated message..."></textarea>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info btn-sm text-white" id="confirm-edit-btn"><i class="fas fa-save me-1"></i> Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .glass-card {
                background: rgba(255, 255, 255, 0.6) !important;
                backdrop-filter: blur(14px) !important;
                -webkit-backdrop-filter: blur(14px) !important;
                border: 1px solid rgba(255, 255, 255, 0.4) !important;
                border-radius: 20px !important;
                box-shadow: 0 10px 40px 0 rgba(0, 0, 0, 0.04) !important;
            }

            .glass-input {
                background: rgba(255, 255, 255, 0.8) !important;
                backdrop-filter: blur(8px) !important;
                border: 1px solid rgba(255, 255, 255, 0.5) !important;
                border-radius: 12px !important;
                color: #3f4254 !important;
                padding-left: 3rem !important;
                /* space for search icon */
            }

            .glass-input:focus {
                background: #ffffff !important;
                box-shadow: 0 0 15px rgba(80, 20, 208, 0.1) !important;
                border-color: rgba(80, 20, 208, 0.3) !important;
            }

            .glass-button {
                background: linear-gradient(135deg, #181C32, #181C32) !important;
                /* using a dark subtle solid instead of garish gradient for sleekness */
                border: none !important;
                color: white !important;
                box-shadow: 0 4px 10px rgba(24, 28, 50, 0.2) !important;
                transition: all 0.3s ease;
            }

            .glass-button:hover {
                transform: translateY(-1px);
                box-shadow: 0 6px 15px rgba(24, 28, 50, 0.3) !important;
            }

            .chat-contact-item {
                transition: all 0.2s ease;
                border-radius: 14px;
                border: 1px solid transparent;
            }

            .chat-contact-item.active {
                background-color: #ffffff;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
                border: 1px solid rgba(0, 0, 0, 0.04);
            }

            .chat-contact-item:hover:not(.active) {
                background-color: rgba(255, 255, 255, 0.6);
            }

            /* Custom scrollbar for chat */
            .kt-scroll-content::-webkit-scrollbar,
            .chat-messages-container::-webkit-scrollbar {
                width: 5px;
            }

            .kt-scroll-content::-webkit-scrollbar-thumb,
            .chat-messages-container::-webkit-scrollbar-thumb {
                background-color: rgba(0, 0, 0, 0.08);
                border-radius: 4px;
            }

            .chat-messages-container::-webkit-scrollbar-track {
                background: transparent;
            }

            .message-in {
                background-color: #ffffff;
                border-radius: 0 16px 16px 16px;
                padding: 14px 18px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
                color: #4B5675;
                font-weight: 500;
                line-height: 1.5;
                border: 1px solid rgba(0, 0, 0, 0.02);
                max-width: 80%;
            }

            .message-out {
                background-color: #F1F1F4;
                border-radius: 16px 0 16px 16px;
                padding: 14px 18px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
                color: #181C32;
                font-weight: 500;
                line-height: 1.5;
                max-width: 80%;
            }

            .message-out img,
            .message-out video,
            .message-in img,
            .message-in video {
                width: 250px;
                height: 250px;
                object-fit: cover;
                border-radius: 12px;
                background-color: #000;
                border: 1px solid rgba(0, 0, 0, 0.05);
            }

            .media-attachment-container {
                overflow: hidden;
                border-radius: 12px;
            }

            .media-actions {
                opacity: 0;
                transition: opacity 0.3s ease;
                background: rgba(0, 0, 0, 0.4);
                display: flex;
                flex-direction: row;
                justify-content: center;
                align-items: center;
                gap: 10px;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 5;
            }

            .media-attachment-container:hover .media-actions {
                opacity: 1 !important;
            }

            .media-attachment-container img,
            .media-attachment-container video {
                transition: transform 0.3s;
            }

            .media-attachment-container:hover img,
            .media-attachment-container:hover video {
                transform: scale(1.05);
            }

            .shared-media-item {
                width: 100%;
                aspect-ratio: 1;
                object-fit: cover;
                border-radius: 8px;
                cursor: pointer;
                transition: transform 0.2s;
                border: 1px solid rgba(0, 0, 0, 0.05);
            }

            .shared-media-item:hover {
                transform: scale(1.03);
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            }

            .chat-msg-row .action-dots-btn {
                opacity: 0;
                transition: opacity 0.2s ease-in-out;
            }

            .chat-msg-row:hover .action-dots-btn,
            .chat-msg-row .action-dots-btn:focus,
            .chat-msg-row .dropdown.show .action-dots-btn {
                opacity: 1 !important;
            }

            .glass-dropdown {
                background: rgba(255, 255, 255, 0.95) !important;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(0, 0, 0, 0.08) !important;
                border-radius: 12px !important;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
            }
        </style>
    @endpush

    @push('script')
        <!-- Pusher & Echo via CDN -->
        <script src="https://js.pusher.com/8.0/pusher.min.js"></script>
        <script>
            // Set pusher logging
            Pusher.logToConsole = true;

            // Reuse existing pusher instance if available to maintain navbar notification connection
            if (typeof pusher === 'undefined') {
                var pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
                    cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
                    forceTLS: true,
                    authEndpoint: '{{ route('admin.pusher.auth') }}',
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }
                });

                pusher.connection.bind('error', function(err) {
                    console.error('Pusher connection error:', err);
                    if (err.error && err.error.data && err.error.data.code === 4004) {
                        console.error('Over limit!');
                    }
                });

                pusher.connection.bind('state_change', function(states) {
                    console.log('Pusher state change:', states);
                });
            }

            const currentUserId = {{ auth()->id() }};
            let currentChatId = null;
            let currentIsGroup = false;
            let selectedFile = null;
            let selectedFileType = 'text';
            let currentReplyMessageId = null;

            function escapeJsString(str) {
                if (!str) return '';
                return str.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, ' ');
            }

            function escapeHtml(str) {
                if (!str) return '';
                return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
            }

            // Media Recorder variables
            let mediaRecorder;
            let audioChunks = [];
            let isRecording = false;
            let recordTimer;
            let recordSeconds = 0;

            // Elements
            const activeChatNameEl = document.getElementById('active-chat-name');
            const messagesContainer = document.querySelector('.chat-messages-container');
            const chatFooter = document.getElementById('kt_chat_messenger_footer');
            const chatForm = document.getElementById('chat-form');
            const messageInput = document.getElementById('message-input');

            const previewArea = document.getElementById('attachment-preview');
            const imgPreview = document.getElementById('img-preview');
            const vidPreview = document.getElementById('vid-preview');
            const audPreview = document.getElementById('aud-preview');
            const clearBtn = document.getElementById('clear-attachment');

            // Search filter
            document.querySelector('[name="search"]').addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                document.querySelectorAll('.chat-contact-item').forEach(el => {
                    const name = el.querySelector('.chat-name-label').textContent.toLowerCase();
                    if (name.includes(term)) {
                        el.style.setProperty('display', 'flex', 'important');
                    } else {
                        el.style.setProperty('display', 'none', 'important');
                    }
                });
            });

            // Contact Click Event
            document.querySelectorAll('.chat-contact-item').forEach(item => {
                item.addEventListener('click', function() {
                    // Remove active class from all
                    document.querySelectorAll('.chat-contact-item').forEach(el => el.classList.remove(
                        'active'));
                    this.classList.add('active');

                    const isUserItem = this.hasAttribute('data-user-id');
                    let chatImageSrc = this.querySelector('img').src;

                    if (isUserItem) {
                        // Create or load chat with user
                        const userId = this.getAttribute('data-user-id');
                        const userName = this.getAttribute('data-user-name');

                        // Switch to Recent Chats tab visually (optional, but good UX)
                        document.querySelector('a[href="#kt_chat_recent"]').click();

                        startChatWithUser(userId, userName, chatImageSrc);
                        return;
                    }

                    const chatId = this.getAttribute('data-chat-id');
                    const chatName = this.getAttribute('data-chat-name');
                    const isGroup = this.getAttribute('data-is-group') === 'true';

                    // If same chat clicked, do nothing
                    if (currentChatId == chatId) return;

                    // Unsubscribe from previous if exists
                    if (currentChatId) {
                        pusher.unsubscribe('private-chat.' + currentChatId);
                    }

                    currentChatId = chatId;
                    currentIsGroup = isGroup;
                    activeChatNameEl.textContent = chatName;

                    // Reset Info Sidebar if open
                    const sidebar = document.getElementById('chat-info-sidebar');
                    if (sidebar.classList.contains('d-flex')) {
                        updateInfoSidebar();
                    }

                    // Show header details
                    document.getElementById('active-chat-status').classList.remove('d-none');
                    document.getElementById('active-chat-avatar-container').classList.remove('d-none');
                    document.getElementById('active-chat-avatar').src = chatImageSrc;
                    document.getElementById('chat-header-actions').classList.remove('d-none');

                    chatFooter.style.display = 'block';

                    loadMessages(chatId);
                    subscribeToChat(chatId);
                });
            });

            function startChatWithUser(userId, userName, chatImageSrc) {
                messagesContainer.innerHTML =
                    '<div class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border text-primary" role="status"></div></div>';

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('user_id', userId);

                fetch(`/admin/chat/start`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status) {
                            const chatId = data.data.id;

                            // Check if chat is already in the list to activate it
                            const existingChatEl = document.querySelector(`.chat-contact-item[data-chat-id="${chatId}"]`);
                            if (existingChatEl) {
                                document.querySelectorAll('.chat-contact-item').forEach(el => el.classList.remove(
                                    'active'));
                                existingChatEl.classList.add('active');
                            }

                            if (currentChatId == chatId) return;

                            if (currentChatId) {
                                pusher.unsubscribe('private-chat.' + currentChatId);
                            }

                            currentChatId = chatId;
                            activeChatNameEl.textContent = userName;

                            document.getElementById('active-chat-status').classList.remove('d-none');
                            document.getElementById('active-chat-avatar-container').classList.remove('d-none');
                            document.getElementById('active-chat-avatar').src = chatImageSrc;
                            document.getElementById('chat-header-actions').classList.remove('d-none');

                            chatFooter.style.display = 'block';

                            loadMessages(chatId);
                            subscribeToChat(chatId);
                        }
                    });
            }

            // Load Messages via AJAX
            function loadMessages(chatId) {
                messagesContainer.innerHTML =
                    '<div class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border text-primary" role="status"></div></div>';

                clearSharedMedia();

                fetch(`/admin/chat/${chatId}/messages`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status) {
                            messagesContainer.innerHTML = '';
                            data.data.forEach(msg => {
                                appendMessage(msg);
                            });
                            scrollToBottom();
                        }
                    });
            }

            function subscribeToChat(chatId) {
                var channel = pusher.subscribe(`private-chat.${chatId}`);
                channel.bind('message.sent', function(data) {
                    if (data.message.chat_id == currentChatId) {
                        // Only append if it's NOT from current user (to avoid duplication since we optimistically add ours)
                        if (data.message.sender_id != currentUserId) {
                            appendMessage(data.message);
                            scrollToBottom();
                        }
                    }
                });
            }

            function appendMessage(msg) {
                const isMe = msg.sender_id === currentUserId;
                let time = new Date(msg.created_at).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                let attachmentHtml = '';
                if (msg.attachment) {
                    const url = '/storage/' + msg.attachment;
                    let mediaType = msg.type;

                    if (mediaType === 'image') {
                        attachmentHtml = `
                        <div class="position-relative media-attachment-container d-inline-block" style="width: 250px; height: 250px; max-width: 100%;">
                            <img src="${url}" class="rounded" style="width: 100% !important; height: 100% !important; object-fit: cover !important; display: block;">
                            <div class="media-actions rounded">
                                <button type="button" class="btn btn-icon btn-light btn-sm shadow-sm" style="border-radius: 50%;" title="View" onclick="openImageModal('${url}')"><i class="fas fa-eye text-dark"></i></button>
                                <a href="${url}" download class="btn btn-icon btn-light btn-sm shadow-sm" style="border-radius: 50%;" title="Download"><i class="fas fa-download text-dark"></i></a>
                            </div>
                        </div>`;
                        addSharedMedia(url, 'image');
                    } else if (mediaType === 'video') {
                        attachmentHtml = `
                        <div class="position-relative media-attachment-container d-inline-block" style="width: 250px; height: 250px; max-width: 100%;">
                            <video src="${url}" class="rounded bg-dark" controls preload="metadata" style="width: 100% !important; height: 100% !important; object-fit: cover !important; display: block;"></video>
                            <div class="media-actions rounded" style="pointer-events: none;">
                                <div style="pointer-events: auto; display: flex; gap: 10px;">
                                    <a href="${url}" target="_blank" class="btn btn-icon btn-light btn-sm shadow-sm" style="border-radius: 50%;" title="View"><i class="fas fa-external-link-alt text-dark fs-5"></i></a>
                                    <a href="${url}" download class="btn btn-icon btn-light btn-sm shadow-sm" style="border-radius: 50%;" title="Download"><i class="fas fa-download text-dark fs-5"></i></a>
                                </div>
                            </div>
                        </div>`;
                        addSharedMedia(url, 'video');
                    } else if (mediaType === 'audio' || mediaType === 'recorded_audio') {
                        attachmentHtml = `<audio src="${url}" controls></audio>`;
                    }
                }

                // Avatar
                let avatarHtml = '';
                if (!isMe) {
                    const senderAvatar = (msg.sender && msg.sender.image_path) ? msg.sender.image_path : '{{ asset('assets/media/svg/avatars/001-boy.svg') }}';
                    avatarHtml = `
                    <div class="symbol symbol-35px symbol-circle me-3 mt-1">
                        <img alt="Pic" src="${senderAvatar}">
                    </div>`;
                }

                // Reply Quote
                let replyQuoteHtml = '';
                const replyMsg = msg.reply_to_message || msg.replyToMessage;
                if (replyMsg) {
                    const replySender = replyMsg.sender ? replyMsg.sender.name : 'Message';
                    const replyText = replyMsg.message ? replyMsg.message : (replyMsg.attachment ? '[Attachment]' : '');
                    replyQuoteHtml = `
                        <div class="reply-quote-box border-start border-3 border-primary ps-2 py-1 mb-2 bg-light-primary rounded text-dark fs-8">
                            <div class="fw-bold text-primary">${escapeHtml(replySender)}</div>
                            <div class="text-truncate" style="max-width: 250px;">${escapeHtml(replyText)}</div>
                        </div>
                    `;
                }

                // Badges
                let badgesHtml = '';
                if (msg.is_pinned) {
                    badgesHtml += `<span class="badge badge-light-warning me-1 fs-9"><i class="fas fa-thumbtack me-1 text-warning"></i> Pinned</span>`;
                }
                if (msg.is_forwarded) {
                    badgesHtml += `<span class="badge badge-light-secondary me-1 fs-9"><i class="fas fa-share me-1 text-secondary"></i> Forwarded</span>`;
                }

                let editedHtml = msg.is_edited ? `<span class="text-muted fs-9 ms-1">(edited)</span>` : '';

                let messageContentHtml = '';
                if (msg.message && msg.message.trim() !== '') {
                    const formattedMsg = escapeHtml(msg.message).replace(/\n/g, '<br>');
                    messageContentHtml = `<div class="${msg.attachment ? 'mt-3' : ''} fs-6">${formattedMsg}${editedHtml}</div>`;
                } else if (editedHtml && !attachmentHtml) {
                    messageContentHtml = `<div class="fs-6">${editedHtml}</div>`;
                }

                const senderName = isMe ? 'You' : (msg.sender ? msg.sender.name : 'User');
                const escapedMsgText = escapeJsString(msg.message || '');
                const escapedSenderName = escapeJsString(senderName);

                // 3-dot action menu on hover
                const actionsMenuHtml = `
                    <div class="dropdown message-actions-dropdown ${isMe ? 'me-2 order-first' : 'ms-2'} align-self-center">
                        <button class="btn btn-icon btn-sm btn-active-light-primary rounded-circle action-dots-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Options" style="width: 28px; height: 28px;">
                            <i class="fas fa-ellipsis-v text-muted fs-7"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-${isMe ? 'end' : 'start'} glass-dropdown fs-7" style="z-index: 1000;">
                            <li><a class="dropdown-item reply-msg-action" href="javascript:void(0)" onclick="initiateReply(${msg.id}, '${escapedSenderName}', '${escapedMsgText}')"><i class="fas fa-reply me-2 text-primary"></i> Reply</a></li>
                            ${(msg.type === 'text') ? `<li><a class="dropdown-item pin-msg-action" href="javascript:void(0)" onclick="togglePinMessage(${msg.id})"><i class="fas fa-thumbtack me-2 text-warning"></i> ${msg.is_pinned ? 'Unpin' : 'Pin'}</a></li>` : ''}
                            ${isMe ? `<li><a class="dropdown-item edit-msg-action" href="javascript:void(0)" onclick="initiateEdit(${msg.id}, '${escapedMsgText}')"><i class="fas fa-edit me-2 text-info"></i> Edit</a></li>` : ''}
                            <li><a class="dropdown-item forward-msg-action" href="javascript:void(0)" onclick="initiateForward(${msg.id})"><i class="fas fa-share me-2 text-success"></i> Forward</a></li>
                            ${isMe ? `<li><hr class="dropdown-divider"></li><li><a class="dropdown-item text-danger delete-msg-action" href="javascript:void(0)" onclick="deleteMessage(${msg.id})"><i class="fas fa-trash me-2 text-danger"></i> Delete</a></li>` : ''}
                        </ul>
                    </div>
                `;

                const html = `
                <div class="d-flex justify-content-${isMe ? 'end' : 'start'} mb-6 chat-msg-row" data-message-id="${msg.id}">
                    <div class="d-flex flex-row align-items-start max-w-100 max-w-md-80">
                        ${avatarHtml}
                        <div class="d-flex flex-column align-items-${isMe ? 'end' : 'start'}">
                            <div class="d-flex align-items-center mb-1">
                                <span class="fs-6 fw-bold text-gray-800 me-2">${senderName}</span>
                                ${badgesHtml}
                                <span class="text-muted fs-8">${time}</span>
                            </div>
                            <div class="d-flex flex-row align-items-center">
                                ${actionsMenuHtml}
                                <div class="${isMe ? 'message-out' : 'message-in'}">
                                    ${replyQuoteHtml}
                                    ${attachmentHtml}
                                    ${messageContentHtml}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
                messagesContainer.insertAdjacentHTML('beforeend', html);
            }

            // Action Functions
            const replyPreviewBar = document.getElementById('reply-preview-bar');
            const replyPreviewName = document.getElementById('reply-preview-name');
            const replyPreviewText = document.getElementById('reply-preview-text');
            const cancelReplyBtn = document.getElementById('cancel-reply-btn');

            function initiateReply(id, senderName, text) {
                currentReplyMessageId = id;
                if (replyPreviewName) replyPreviewName.textContent = `Replying to ${senderName}`;
                if (replyPreviewText) replyPreviewText.textContent = text || '[Attachment]';
                if (replyPreviewBar) replyPreviewBar.classList.remove('d-none');
                if (messageInput) messageInput.focus();
            }

            function cancelReply() {
                currentReplyMessageId = null;
                if (replyPreviewBar) replyPreviewBar.classList.add('d-none');
            }

            if (cancelReplyBtn) {
                cancelReplyBtn.addEventListener('click', cancelReply);
            }

            function initiateEdit(id, text) {
                document.getElementById('edit-message-id').value = id;
                document.getElementById('edit-message-text').value = text;
                const editModalEl = document.getElementById('chat_edit_modal');
                if (editModalEl) {
                    const editModal = new bootstrap.Modal(editModalEl);
                    editModal.show();
                }
            }

            document.getElementById('confirm-edit-btn')?.addEventListener('click', function() {
                const id = document.getElementById('edit-message-id').value;
                const newText = document.getElementById('edit-message-text').value;

                if (!newText.trim()) return;

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('message', newText);

                fetch(`/admin/chat/message/${id}/edit`, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status) {
                        const editModalEl = document.getElementById('chat_edit_modal');
                        if (editModalEl) bootstrap.Modal.getInstance(editModalEl)?.hide();
                        if (currentChatId) loadMessages(currentChatId);
                    }
                });
            });

            function deleteMessage(id) {
                if (!confirm('Are you sure you want to delete this message?')) return;

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('_method', 'DELETE');

                fetch(`/admin/chat/message/${id}/delete`, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status && currentChatId) {
                        loadMessages(currentChatId);
                    }
                });
            }

            function togglePinMessage(id) {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');

                fetch(`/admin/chat/message/${id}/pin`, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status && currentChatId) {
                        loadMessages(currentChatId);
                    } else if (!data.status) {
                        alert(data.message || 'Error pinning message');
                    }
                });
            }

            function initiateForward(id) {
                document.getElementById('forward-message-id').value = id;
                const container = document.getElementById('forward-chat-list');
                if (!container) return;
                container.innerHTML = '';

                const contacts = document.querySelectorAll('.chat-contact-item[data-chat-id]');
                if (!contacts.length) {
                    container.innerHTML = '<div class="text-muted fs-8">No active chats available to forward.</div>';
                } else {
                    contacts.forEach(item => {
                        const chatId = item.getAttribute('data-chat-id');
                        const chatName = item.getAttribute('data-chat-name');
                        const imgSrc = item.querySelector('img')?.src || '';

                        container.innerHTML += `
                            <div class="form-check d-flex align-items-center mb-3">
                                <input class="form-check-input me-3 forward-chat-checkbox" type="checkbox" value="${chatId}" id="fwd_chat_${chatId}">
                                <label class="form-check-label d-flex align-items-center cursor-pointer" for="fwd_chat_${chatId}">
                                    <img src="${imgSrc}" class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                    <span class="fw-semibold text-gray-800">${escapeHtml(chatName)}</span>
                                </label>
                            </div>
                        `;
                    });
                }

                const forwardModalEl = document.getElementById('chat_forward_modal');
                if (forwardModalEl) {
                    const forwardModal = new bootstrap.Modal(forwardModalEl);
                    forwardModal.show();
                }
            }

            document.getElementById('confirm-forward-btn')?.addEventListener('click', function() {
                const id = document.getElementById('forward-message-id').value;
                const checked = Array.from(document.querySelectorAll('.forward-chat-checkbox:checked')).map(cb => cb.value);

                if (!checked.length) {
                    alert('Please select at least one chat to forward to.');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                checked.forEach(chatId => formData.append('target_chat_ids[]', chatId));

                fetch(`/admin/chat/message/${id}/forward`, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status) {
                        const forwardModalEl = document.getElementById('chat_forward_modal');
                        if (forwardModalEl) bootstrap.Modal.getInstance(forwardModalEl)?.hide();
                        if (currentChatId && checked.includes(currentChatId.toString())) {
                            loadMessages(currentChatId);
                        }
                    }
                });
            });

            function scrollToBottom() {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            // File Inputs logic
            document.getElementById('image-upload').addEventListener('change', function(e) {
                handleFileSelect(e, 'image');
            });
            document.getElementById('video-upload').addEventListener('change', function(e) {
                handleFileSelect(e, 'video');
            });

            function handleFileSelect(e, type) {
                if (e.target.files.length) {
                    selectedFile = e.target.files[0];
                    selectedFileType = type;
                    previewArea.classList.remove('d-none');

                    imgPreview.style.display = 'none';
                    vidPreview.style.display = 'none';
                    audPreview.style.display = 'none';

                    let url = URL.createObjectURL(selectedFile);
                    if (type === 'image') {
                        imgPreview.src = url;
                        imgPreview.style.display = 'block';
                    }
                    if (type === 'video') {
                        vidPreview.src = url;
                        vidPreview.style.display = 'block';
                    }
                }
            }

            clearBtn.addEventListener('click', function() {
                selectedFile = null;
                selectedFileType = 'text';
                previewArea.classList.add('d-none');
                document.getElementById('image-upload').value = '';
                document.getElementById('video-upload').value = '';
            });

            // Audio Recording
            const recordBtn = document.getElementById('record-audio-btn');
            recordBtn.addEventListener('click', async () => {
                if (isRecording) {
                    mediaRecorder.stop();
                    isRecording = false;
                    recordBtn.innerHTML = '<i class="fas fa-microphone fs-3"></i>';
                    recordBtn.classList.remove('btn-danger');
                    document.getElementById('recording-indicator').classList.add('d-none');
                    clearInterval(recordTimer);
                } else {
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({
                            audio: true
                        });
                        mediaRecorder = new MediaRecorder(stream);
                        audioChunks = [];

                        mediaRecorder.addEventListener("dataavailable", event => {
                            audioChunks.push(event.data);
                        });

                        mediaRecorder.addEventListener("stop", () => {
                            const audioBlob = new Blob(audioChunks, {
                                type: 'audio/webm'
                            });
                            selectedFile = audioBlob;
                            selectedFileType = 'recorded_audio';

                            // Preview
                            previewArea.classList.remove('d-none');
                            imgPreview.style.display = 'none';
                            vidPreview.style.display = 'none';
                            audPreview.src = URL.createObjectURL(audioBlob);
                            audPreview.style.display = 'block';
                        });

                        mediaRecorder.start();
                        isRecording = true;
                        recordBtn.innerHTML = '<i class="fas fa-stop fs-3"></i>';
                        recordBtn.classList.add('btn-danger');

                        // Timer
                        recordSeconds = 0;
                        document.getElementById('recording-indicator').classList.remove('d-none');
                        document.getElementById('recording-time').textContent = '0:00';
                        recordTimer = setInterval(() => {
                            recordSeconds++;
                            const mins = Math.floor(recordSeconds / 60);
                            const secs = recordSeconds % 60;
                            document.getElementById('recording-time').textContent =
                                `${mins}:${secs.toString().padStart(2, '0')}`;
                        }, 1000);

                    } catch (err) {
                        alert('Microphone access denied or not available.');
                    }
                }
            });

            // Submit message
            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (!currentChatId) return;

                const msgText = messageInput.value;
                if (!msgText && !selectedFile) return;

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('type', selectedFileType);

                if (msgText) formData.append('message', msgText);
                if (currentReplyMessageId) {
                    formData.append('reply_to_message_id', currentReplyMessageId);
                }
                if (selectedFile) {
                    // If blob (recorded audio), give it a filename
                    if (selectedFile instanceof Blob && !selectedFile.name) {
                        formData.append('attachment', selectedFile, 'audio.webm');
                    } else {
                        formData.append('attachment', selectedFile);
                    }
                }

                cancelReply();

                // Optimistic UI for text
                if (selectedFileType === 'text') {
                    appendMessage({
                        sender_id: currentUserId,
                        created_at: new Date().toISOString(),
                        message: msgText,
                        type: 'text',
                        attachment: null,
                        sender: {
                            name: 'You',
                            image_path: '{{ auth()->user()->image_path }}'
                        }
                    });
                    scrollToBottom();
                }

                const currentType = selectedFileType;
                messageInput.value = '';
                clearBtn.click(); // Clear previews

                fetch(`/admin/chat/${currentChatId}/send`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status) {
                            // Only append if it wasn't already appended optimistically (which is only for 'text' currently)
                            if (currentType !== 'text') {
                                appendMessage(data.data);
                                scrollToBottom();
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Error sending message');
                    });
            });
            // Enter to send
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (messageInput.value.trim() !== '' || selectedFile) {
                        chatForm.dispatchEvent(new Event('submit'));
                    }
                }
            });

            // Auto resize textarea
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
                if (this.value === '') {
                    this.style.height = 'auto';
                }
            });

            // Shared Media logic
            document.getElementById('toggle-info-btn').addEventListener('click', function() {
                var sidebar = document.getElementById('chat-info-sidebar');
                if (sidebar.classList.contains('d-none')) {
                    sidebar.classList.remove('d-none');
                    sidebar.classList.add('d-flex');
                    updateInfoSidebar();
                } else {
                    sidebar.classList.add('d-none');
                    sidebar.classList.remove('d-flex');
                }
            });

            function updateInfoSidebar() {
                document.getElementById('info-chat-name').textContent = document.getElementById('active-chat-name')
                    .textContent;
                document.getElementById('info-chat-avatar').src = document.getElementById('active-chat-avatar').src;

                const membersTitle = document.getElementById('members-section-title');
                const membersContainer = document.getElementById('members-list-container');

                if (currentIsGroup) {
                    membersTitle.style.display = 'block';
                    membersContainer.style.display = 'block';
                    loadParticipants(currentChatId);
                } else {
                    membersTitle.style.display = 'none';
                    membersContainer.style.display = 'none';
                    membersContainer.innerHTML = '';
                }
            }

            function loadParticipants(chatId) {
                const container = document.getElementById('members-list-container');
                container.innerHTML =
                    '<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

                fetch(`/admin/chat/${chatId}/participants`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status) {
                            container.innerHTML = '';
                            data.data.forEach(user => {
                                const html = `
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="symbol symbol-35px symbol-circle me-3">
                                            <img src="${user.image_path}" alt="user">
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fs-7 fw-bold text-gray-900">${user.name}</span>
                                            <span class="fs-9 text-muted">${user.email}</span>
                                        </div>
                                    </div>
                                `;
                                container.insertAdjacentHTML('beforeend', html);
                            });
                        }
                    });
            }

            document.getElementById('close-info-btn').addEventListener('click', function() {
                document.getElementById('chat-info-sidebar').classList.add('d-none');
                document.getElementById('chat-info-sidebar').classList.remove('d-flex');
            });

            function clearSharedMedia() {
                document.getElementById('shared-media-grid').innerHTML =
                    '<div class="text-center text-muted py-5 w-100" id="no-media-msg">No media shared yet.</div>';
            }

            function addSharedMedia(url, type) {
                const noMediaMsg = document.getElementById('no-media-msg');
                if (noMediaMsg) noMediaMsg.remove();

                let mediaElement = '';
                if (type === 'image') {
                    mediaElement =
                        `<div class="position-relative media-attachment-container d-inline-block" style="width: 90px; height: 90px;">
                            <img src="${url}" class="rounded" style="width: 100% !important; height: 100% !important; object-fit: cover !important; display: block;">
                            <div class="media-actions rounded">
                                <button type="button" class="btn btn-icon btn-light btn-sm shadow-sm" style="border-radius: 50%; width: 24px; height: 24px;" title="View" onclick="openImageModal('${url}')"><i class="fas fa-eye text-dark" style="font-size: 10px;"></i></button>
                                <a href="${url}" download class="btn btn-icon btn-light btn-sm shadow-sm" style="border-radius: 50%; width: 24px; height: 24px;" title="Download"><i class="fas fa-download text-dark" style="font-size: 10px;"></i></a>
                            </div>
                        </div>`;
                } else if (type === 'video') {
                    mediaElement =
                        `<div class="position-relative media-attachment-container d-inline-block" style="width: 90px; height: 90px;">
                            <video src="${url}" class="rounded bg-dark" preload="metadata" style="width: 100% !important; height: 100% !important; object-fit: cover !important; display: block;"></video>
                            <div class="media-actions rounded" style="pointer-events: none;">
                                <div style="pointer-events: auto; display: flex; gap: 5px;">
                                    <a href="${url}" target="_blank" class="btn btn-icon btn-light btn-sm shadow-sm" style="border-radius: 50%; width: 24px; height: 24px;" title="View"><i class="fas fa-external-link-alt text-dark" style="font-size: 10px;"></i></a>
                                    <a href="${url}" download class="btn btn-icon btn-light btn-sm shadow-sm" style="border-radius: 50%; width: 24px; height: 24px;" title="Download"><i class="fas fa-download text-dark" style="font-size: 10px;"></i></a>
                                </div>
                            </div>
                        </div>`;
                }

                const colHtml = `
                <div class="col-auto position-relative">
                    ${mediaElement}
                </div>`;

                document.getElementById('shared-media-grid').insertAdjacentHTML('afterbegin', colHtml);
            }

            function openImageModal(url) {
                document.getElementById('chat_modal_image').src = url;
                var myModal = new bootstrap.Modal(document.getElementById('chat_image_modal'));
                myModal.show();
            }

            // Handle chat_id from URL for notification redirection
            const urlParams = new URLSearchParams(window.location.search);
            const chatIdParam = urlParams.get('chat_id');
            if (chatIdParam) {
                // Try to find in recent chats first
                let contactItem = document.querySelector(`.chat-contact-item[data-chat-id="${chatIdParam}"]`);
                if (contactItem) {
                    contactItem.click();
                } else {
                    // If not found in recent, maybe it's in the Users tab? 
                    // Or we just wait a bit if it's dynamic
                    setTimeout(() => {
                        contactItem = document.querySelector(`.chat-contact-item[data-chat-id="${chatIdParam}"]`);
                        if (contactItem) contactItem.click();
                    }, 1000);
                }
            }
        </script>
    @endpush
@endsection
