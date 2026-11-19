@extends('layouts.app', [
    'title' => 'SDM & Penggajian (HRM)',
    'headerTitle' => 'Manajemen SDM & Penggajian',
    'headerSubtitle' => 'Kelola profil staf, biometrik wajah anti-spoofing, kepesertaan BPJS, kasbon, geofencing lokasi, dan penggajian otomatis.',
    'activeModuleKey' => 'hrm',
    'secondaryNavTabs' => [
        [
            'key' => 'employees',
            'label' => __('hrm.tab_employees'),
            'url' => route('hrm.index', ['tab' => 'employees']),
            'is_active' => request('tab', $tab ?? 'employees') === 'employees',
            'icon' => 'users-2',
            'badge' => (string) $totalStaff,
        ],
        [
            'key' => 'attendance',
            'label' => __('hrm.tab_attendance'),
            'url' => route('hrm.index', ['tab' => 'attendance']),
            'is_active' => request('tab') === 'attendance',
            'icon' => 'scan-face',
            'badge' => (string) $todayPresentCount,
        ],
        [
            'key' => 'corrections',
            'label' => __('hrm.tab_corrections'),
            'url' => route('hrm.index', ['tab' => 'corrections']),
            'is_active' => request('tab') === 'corrections',
            'icon' => 'file-check-2',
            'badge' => $pendingCorrectionsCount > 0 ? (string) $pendingCorrectionsCount : ($corrections->total() > 0 ? (string) $corrections->total() : null),
        ],
        [
            'key' => 'locations',
            'label' => __('hrm.tab_locations'),
            'url' => route('hrm.index', ['tab' => 'locations']),
            'is_active' => request('tab') === 'locations',
            'icon' => 'map-pin',
            'badge' => (string) $allLocations->count(),
        ],
        [
            'key' => 'shifts',
            'label' => __('hrm.tab_shifts'),
            'url' => route('hrm.index', ['tab' => 'shifts']),
            'is_active' => request('tab') === 'shifts',
            'icon' => 'clock',
            'badge' => (string) $workShifts->count(),
        ],
        [
            'key' => 'schedules',
            'label' => __('hrm.tab_schedules'),
            'url' => route('hrm.index', ['tab' => 'schedules']),
            'is_active' => request('tab') === 'schedules',
            'icon' => 'calendar',
            'badge' => (string) $employeeSchedules->total(),
        ],
        [
            'key' => 'payrolls',
            'label' => __('hrm.tab_payrolls'),
            'url' => route('hrm.index', ['tab' => 'payrolls']),
            'is_active' => request('tab') === 'payrolls',
            'icon' => 'receipt',
            'badge' => (string) $payrolls->total(),
        ],
        [
            'key' => 'loans',
            'label' => __('hrm.tab_loans'),
            'url' => route('hrm.index', ['tab' => 'loans']),
            'is_active' => request('tab') === 'loans',
            'icon' => 'credit-card',
            'badge' => (string) $loans->total(),
        ],
        [
            'key' => 'portal',
            'label' => 'Portal Presensi Mandiri',
            'url' => route('portal'),
            'is_active' => false,
            'icon' => 'smartphone',
            'badge' => null,
        ],
        [
            'key' => 'simulator',
            'label' => __('hrm.tax_simulator_pph21'),
            'url' => route('tax.index'),
            'is_active' => false,
            'icon' => 'scale',
            'badge' => null,
        ],
    ]
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32" x-data="{
    activeTab: '{{ request('tab', $tab ?? 'employees') }}',
    toast: {
        show: false,
        message: '',
        type: 'success'
    },
    showToast(msg, type = 'success') {
        this.toast.message = msg;
        this.toast.type = type;
        this.toast.show = true;
        setTimeout(() => { this.toast.show = false; }, 4000);
    },
    setTab(tabName) {
        this.activeTab = tabName;
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url.toString());
        if (window.__coocaSyncSecondarySidebar) {
            window.__coocaSyncSecondarySidebar(tabName);
        }
        this.$nextTick(() => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    },

    init() {
        const syncSidebar = (tabKey) => {
            document.querySelectorAll('#secondary-sidebar a[data-subnav-key]').forEach(el => {
                const isCurrent = el.getAttribute('data-subnav-key') === tabKey;
                el.setAttribute('data-active-secondary-nav', isCurrent ? 'true' : 'false');
                el.setAttribute('aria-current', isCurrent ? 'page' : 'false');
                if (isCurrent) {
                    el.className = 'secondary-subnav-link group w-full flex items-center justify-between px-2.5 h-[38px] rounded-[11px] text-[13px] transition-all active:scale-[0.98] bg-[#007AFF] text-white font-semibold shadow-sm shadow-[#007AFF]/25 is-active';
                    const iconBox = el.querySelector('.subnav-icon-box');
                    if (iconBox) {
                        iconBox.className = 'subnav-icon-box w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 bg-white/20 text-white';
                        const icon = iconBox.querySelector('i, svg');
                        if (icon) {
                            icon.classList.remove('text-[#007AFF]', 'dark:text-[#0A84FF]');
                            icon.classList.add('text-white');
                        }
                    }
                    const badge = el.querySelector('.subnav-badge');
                    if (badge) {
                        badge.className = 'subnav-badge px-2 py-0.5 rounded-full text-[10px] font-bold tabular-nums shrink-0 bg-white/20 text-white';
                    }
                    const chevron = el.querySelector('.subnav-active-chevron');
                    if (chevron) {
                        chevron.classList.remove('hidden');
                    }
                } else {
                    el.className = 'secondary-subnav-link group w-full flex items-center justify-between px-2.5 h-[38px] rounded-[11px] text-[13px] transition-all active:scale-[0.98] text-[#1C1C1E]/80 dark:text-[#F2F2F7]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white font-medium';
                    const iconBox = el.querySelector('.subnav-icon-box');
                    if (iconBox) {
                        iconBox.className = 'subnav-icon-box w-7 h-7 rounded-[8px] flex items-center justify-center shrink-0 transition-all duration-150 bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] group-hover:bg-[#007AFF]/15 group-hover:scale-105';
                        const icon = iconBox.querySelector('i, svg');
                        if (icon) {
                            icon.classList.add('text-[#007AFF]', 'dark:text-[#0A84FF]');
                            icon.classList.remove('text-white');
                        }
                    }
                    const badge = el.querySelector('.subnav-badge');
                    if (badge) {
                        badge.className = 'subnav-badge px-2 py-0.5 rounded-full text-[10px] font-bold tabular-nums shrink-0 bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF]';
                    }
                    const chevron = el.querySelector('.subnav-active-chevron');
                    if (chevron) {
                        chevron.classList.add('hidden');
                    }
                }
            });
        };

        window.__coocaSyncSecondarySidebar = syncSidebar;

        window.__coocaSubnavClick = (key, url, event) => {
            const validTabs = ['employees', 'attendance', 'corrections', 'locations', 'shifts', 'schedules', 'payrolls', 'loans'];
            if (!validTabs.includes(key)) {
                return false;
            }
            this.setTab(key);
            return true;
        };

        window.addEventListener('popstate', () => {
            const params = new URLSearchParams(window.location.search);
            const tabFromUrl = params.get('tab') || 'employees';
            if (this.activeTab !== tabFromUrl) {
                this.setTab(tabFromUrl);
            }
        });
    },

    formatCurrency(num) {
        return new Intl.NumberFormat(window.COOCA_LOCALE === 'en' ? 'en-US' : 'id-ID').format(num || 0);
    },

    // ==========================================
    // SPRINT 3: IMPORT DATA KARYAWAN & ATTENDANCE
    // ==========================================
    showImportEmployeeModal: false,
    importEmployeeFile: null,
    importEmployeeFileName: '',
    importEmployeePreview: null,
    importEmployeeFilter: 'all',
    importEmployeeDuplicateStrategy: 'skip',
    importEmployeeLoading: false,
    importEmployeeExecuting: false,

    handleImportEmployeeFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            this.importEmployeeFile = file;
            this.importEmployeeFileName = file.name;
            this.uploadAndPreviewEmployees();
        }
    },

    uploadAndPreviewEmployees() {
        if (!this.importEmployeeFile) return;
        this.importEmployeeLoading = true;
        const formData = new FormData();
        formData.append('file', this.importEmployeeFile);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route('hrm.employees.import-preview') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => { throw new Error(err.message || '{{ addslashes(__('hrm.err_process_excel_emp')) }}'); });
            }
            return res.json();
        })
        .then(res => {
            this.importEmployeeLoading = false;
            if (res.success) {
                this.importEmployeePreview = res.data;
            } else {
                alert(res.message || '{{ addslashes(__('hrm.err_analyze_file_emp')) }}');
            }
        })
        .catch(err => {
            this.importEmployeeLoading = false;
            alert(err.message || '{{ addslashes(__('hrm.err_upload_file')) }}');
        });
    },

    async executeEmployeeImport() {
        if (!this.importEmployeePreview || !this.importEmployeePreview.rows || this.importEmployeePreview.rows.length === 0) {
            return;
        }

        const totalToProcess = this.importEmployeeDuplicateStrategy === 'skip' 
            ? this.importEmployeePreview.valid_count 
            : (this.importEmployeePreview.valid_count + this.importEmployeePreview.duplicate_count);

        if (totalToProcess <= 0) {
            alert('{{ addslashes(__('hrm.err_no_ready_emp_import')) }}');
            return;
        }

        if (!confirm('{{ addslashes(__('hrm.confirm_process_emp_prefix')) }}' + totalToProcess + '{{ addslashes(__('hrm.confirm_process_emp_suffix')) }}')) {
            return;
        }

        this.importEmployeeExecuting = true;
        fetch('{{ route('hrm.employees.import-execute') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                rows: this.importEmployeePreview.rows,
                duplicate_strategy: this.importEmployeeDuplicateStrategy
            })
        })
        .then(res => res.json())
        .then(res => {
            this.importEmployeeExecuting = false;
            if (res.success) {
                alert(res.message);
                window.location.reload();
            } else {
                alert(res.message || '{{ addslashes(__('hrm.err_execute_import_emp')) }}');
            }
        })
        .catch(err => {
            this.importEmployeeExecuting = false;
            alert(err.message || '{{ addslashes(__('hrm.err_system_import')) }}');
        });
    },

    resetEmployeeImport() {
        this.importEmployeeFile = null;
        this.importEmployeeFileName = '';
        this.importEmployeePreview = null;
        this.importEmployeeFilter = 'all';
        this.importEmployeeDuplicateStrategy = 'skip';
        this.importEmployeeLoading = false;
        this.importEmployeeExecuting = false;
    },

    showImportAttendanceModal: false,
    importAttendanceFile: null,
    importAttendanceFileName: '',
    importAttendancePreview: null,
    importAttendanceFilter: 'all',
    importAttendanceDuplicateStrategy: 'skip',
    importAttendanceLoading: false,
    importAttendanceExecuting: false,

    handleImportAttendanceFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            this.importAttendanceFile = file;
            this.importAttendanceFileName = file.name;
            this.uploadAndPreviewAttendance();
        }
    },

    uploadAndPreviewAttendance() {
        if (!this.importAttendanceFile) return;
        this.importAttendanceLoading = true;
        const formData = new FormData();
        formData.append('file', this.importAttendanceFile);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route('hrm.attendance.import-preview') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => { throw new Error(err.message || '{{ addslashes(__('hrm.err_process_excel_att')) }}'); });
            }
            return res.json();
        })
        .then(res => {
            this.importAttendanceLoading = false;
            if (res.success) {
                this.importAttendancePreview = res.data;
            } else {
                alert(res.message || '{{ addslashes(__('hrm.err_analyze_file_att')) }}');
            }
        })
        .catch(err => {
            this.importAttendanceLoading = false;
            alert(err.message || '{{ addslashes(__('hrm.err_upload_file')) }}');
        });
    },

    async executeAttendanceImport() {
        if (!this.importAttendancePreview || !this.importAttendancePreview.rows || this.importAttendancePreview.rows.length === 0) {
            return;
        }

        const totalToProcess = this.importAttendanceDuplicateStrategy === 'skip' 
            ? this.importAttendancePreview.valid_count 
            : (this.importAttendancePreview.valid_count + this.importAttendancePreview.duplicate_count);

        if (totalToProcess <= 0) {
            alert('{{ addslashes(__('hrm.err_no_ready_att_import')) }}');
            return;
        }

        if (!confirm('{{ addslashes(__('hrm.confirm_process_att_prefix')) }}' + totalToProcess + '{{ addslashes(__('hrm.confirm_process_att_suffix')) }}')) {
            return;
        }

        this.importAttendanceExecuting = true;
        fetch('{{ route('hrm.attendance.import-execute') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                rows: this.importAttendancePreview.rows,
                duplicate_strategy: this.importAttendanceDuplicateStrategy
            })
        })
        .then(res => res.json())
        .then(res => {
            this.importAttendanceExecuting = false;
            if (res.success) {
                alert(res.message);
                window.location.reload();
            } else {
                alert(res.message || '{{ addslashes(__('hrm.err_execute_import_att')) }}');
            }
        })
        .catch(err => {
            this.importAttendanceExecuting = false;
            alert(err.message || '{{ addslashes(__('hrm.err_system_import')) }}');
        });
    },

    resetAttendanceImport() {
        this.importAttendanceFile = null;
        this.importAttendanceFileName = '';
        this.importAttendancePreview = null;
        this.importAttendanceFilter = 'all';
        this.importAttendanceDuplicateStrategy = 'skip';
        this.importAttendanceLoading = false;
        this.importAttendanceExecuting = false;
    },

    showAddEmployeeModal: false,
    showEditEmployeeModal: false,
    showAddLoanModal: false,
    showAddCorrectionModal: false,
    showReviewCorrectionModal: false,
    showFaceRegisterModal: false,
    showFaceAttendanceModal: false,
    showAddLocationModal: false,
    showEditLocationModal: false,
    showAddShiftModal: false,
    showEditShiftModal: false,
    showAddScheduleModal: false,
    showEditScheduleModal: false,
    isSubmittingForm: false,

    selectedEmployee: null,
    selectedCorrection: null,
    selectedFaceEmployee: null,
    selectedLocation: null,
    selectedShift: null,
    selectedSchedule: null,
    editFormAction: '',
    faceRegisterAction: '',
    locationEditAction: '',
    editShiftFormAction: '',
    editScheduleFormAction: '',
    scheduleType: 'recurring',
    isOffDay: false,

    openEditShift(shift, actionUrl) {
        this.selectedShift = shift;
        this.editShiftFormAction = actionUrl;
        this.showEditShiftModal = true;
    },
    openEditSchedule(schedule, actionUrl) {
        this.selectedSchedule = schedule;
        this.editScheduleFormAction = actionUrl;
        this.scheduleType = schedule.schedule_type || 'recurring';
        this.isOffDay = !!schedule.is_off_day;
        this.showEditScheduleModal = true;
    },

    // Geolocation & Live Attendance State
    gpsStatus: 'idle',
    gpsMessage: '{{ addslashes(__('hrm.gps_waiting_signal')) }}',
    currentLat: null,
    currentLng: null,
    currentAccuracy: null,
    distanceMeters: null,
    officeLat: {{ $primaryLocation && $primaryLocation->latitude ? (float) $primaryLocation->latitude : 'null' }},
    officeLng: {{ $primaryLocation && $primaryLocation->longitude ? (float) $primaryLocation->longitude : 'null' }},
    officeRadius: {{ $primaryLocation && $primaryLocation->geofence_radius_meters ? (int) $primaryLocation->geofence_radius_meters : 50 }},
    isFreeLocation: {{ ($currentUserMembership && $currentUserMembership->isFreeLocation()) ? 'true' : 'false' }},
    liveClock: '',
    liveSeconds: '',

    // Biometric Face Registration State
    faceRegMode: 'camera',
    faceRegStream: null,
    faceRegPreview: null,
    faceRegPhotoData: '',

    // 1-Direction Frontal Biometric Attendance State
    attendanceActionType: 'in', // 'in' or 'out'
    attendanceNotes: '',
    liveCameraStream: null,
    stabilityProgress: 0,
    faceDetected: false,
    faceSteady: false,
    faceVerifiedSuccess: false,
    liveDetectionStatus: '{{ addslashes(__('hrm.opening_camera_sensor')) }}',
    liveGuideText: '{{ addslashes(__('hrm.position_face_oval')) }}',
    isVerifyingFace: false,
    faceVerificationResult: null,
    isSubmittingAttendance: false,
    faceCapturedFrame: null,
    attendanceErrorMessage: '',

    init() {
        this.updateClock();
        setInterval(() => this.updateClock(), 1000);
        this.requestLocation();
    },

    updateClock() {
        const now = new Date();
        this.liveClock = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
        this.liveSeconds = String(now.getSeconds()).padStart(2, '0');
    },

    requestLocation() {
        if (!navigator.geolocation) {
            this.gpsStatus = 'error';
            this.gpsMessage = '{{ addslashes(__('hrm.gps_unsupported')) }}';
            return;
        }

        this.gpsStatus = 'locating';
        this.gpsMessage = '{{ addslashes(__('hrm.gps_locating_precision')) }}';

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                this.currentLat = pos.coords.latitude;
                this.currentLng = pos.coords.longitude;
                this.currentAccuracy = Math.round(pos.coords.accuracy);

                if (this.currentAccuracy > 250) {
                    this.gpsStatus = 'error';
                    this.gpsMessage = '{{ addslashes(__('hrm.gps_low_accuracy', ['%s' => ''])) }}'.replace('%s', this.currentAccuracy);
                    return;
                }

                if (this.officeLat && this.officeLng) {
                    this.distanceMeters = this.calcHaversine(this.currentLat, this.currentLng, this.officeLat, this.officeLng);
                }

                this.gpsStatus = 'ready';
                if (this.isFreeLocation) {
                    this.gpsMessage = '{{ addslashes(__('hrm.gps_free_location', ['%s' => ''])) }}'.replace('%s', this.currentAccuracy);
                } else if (this.distanceMeters !== null) {
                    if (this.distanceMeters <= this.officeRadius) {
                        this.gpsMessage = '{{ addslashes(__('hrm.gps_inside_radius', ['%s' => '__DIST__'])) }}'.replace('__DIST__', this.distanceMeters).replace('%sm', this.officeRadius + 'm');
                    } else {
                        this.gpsMessage = '{{ addslashes(__('hrm.gps_outside_radius', ['%s' => '__DIST__'])) }}'.replace('__DIST__', this.distanceMeters).replace('%sm', this.officeRadius + 'm');
                    }
                } else {
                    this.gpsMessage = '{{ addslashes(__('hrm.gps_locked', ['%s' => ''])) }}'.replace('%s', this.currentAccuracy);
                }
            },
            (err) => {
                this.gpsStatus = 'denied';
                if (err.code === 1) {
                    this.gpsMessage = '{{ addslashes(__('hrm.gps_denied')) }}';
                } else {
                    this.gpsMessage = '{{ addslashes(__('hrm.gps_error_prefix')) }}' + err.message;
                }
            },
            { enableHighAccuracy: true, timeout: 12000, maximumAge: 10000 }
        );
    },

    calcHaversine(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return Math.round(R * c);
    },

    openReviewCorrection(item) {
        this.selectedCorrection = item;
        this.showReviewCorrectionModal = true;
    },

    openEditEmployee(membership, updateUrl) {
        this.selectedEmployee = membership;
        this.editFormAction = updateUrl;
        this.showEditEmployeeModal = true;
    },

    openFaceRegister(membership, registerUrl) {
        this.selectedFaceEmployee = membership;
        this.faceRegisterAction = registerUrl;
        this.faceRegPhotoData = '';
        this.faceRegPreview = null;
        this.showFaceRegisterModal = true;
        this.$nextTick(() => {
            if (this.faceRegMode === 'camera') {
                this.startFaceRegCamera();
            }
        });
    },

    startFaceRegCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            const camErr = '{{ addslashes(__('hrm.camera_unsupported')) }}';
            if (window.showAppToast) {
                window.showAppToast('error', camErr);
            } else {
                alert(camErr);
            }
            return;
        }
        navigator.mediaDevices.getUserMedia({ video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' } })
            .then(stream => {
                this.faceRegStream = stream;
                const video = document.getElementById('faceRegVideo');
                if (video) {
                    video.srcObject = stream;
                    video.play();
                }
            })
            .catch(err => {
                console.error('Error camera:', err);
                this.faceRegMode = 'upload';
            });
    },

    stopFaceRegCamera() {
        if (this.faceRegStream) {
            this.faceRegStream.getTracks().forEach(t => t.stop());
            this.faceRegStream = null;
        }
    },

    captureFaceRegSnapshot() {
        const video = document.getElementById('faceRegVideo');
        if (!video) return;
        const canvas = document.createElement('canvas');
        canvas.width = 640;
        canvas.height = 640;
        const ctx = canvas.getContext('2d');
        const vw = video.videoWidth || 640;
        const vh = video.videoHeight || 480;
        const minDim = Math.min(vw, vh);
        const sx = (vw - minDim) / 2;
        const sy = (vh - minDim) / 2;
        ctx.drawImage(video, sx, sy, minDim, minDim, 0, 0, 640, 640);
        this.faceRegPhotoData = canvas.toDataURL('image/jpeg', 0.9);
        this.faceRegPreview = this.faceRegPhotoData;
        this.stopFaceRegCamera();
    },

    handleFaceRegUpload(event) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            this.faceRegPhotoData = e.target.result;
            this.faceRegPreview = e.target.result;
        };
        reader.readAsDataURL(file);
    },

    // Multi-Pose Liveness Recognition Engine
    startFaceAttendance(type = 'in') {
        this.attendanceActionType = type;
        this.poseIndex = 0;
        this.poseProgress = 0;
        this.livenessPassed = false;
        this.faceCapturedFrame = null;
        this.attendanceErrorMessage = '';
        this.showFaceAttendanceModal = true;

        this.$nextTick(() => {
            this.startAttendanceCamera();
        });
    },

    startAttendanceCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            this.attendanceErrorMessage = '{{ addslashes(__('hrm.camera_unsupported_device')) }}';
            return;
        }

        navigator.mediaDevices.getUserMedia({
            video: {
                width: { ideal: 720 },
                height: { ideal: 720 },
                facingMode: 'user'
            }
        }).then(stream => {
            this.liveCameraStream = stream;
            const video = document.getElementById('livenessVideo');
            if (video) {
                video.srcObject = stream;
                video.play();
                this.runPoseLivenessLoop();
            }
        }).catch(err => {
            this.attendanceErrorMessage = '{{ addslashes(__('hrm.camera_permission_denied')) }}' + err.message;
        });
    },

    stopAttendanceCamera() {
        if (this.liveCameraStream) {
            this.liveCameraStream.getTracks().forEach(t => t.stop());
            this.liveCameraStream = null;
        }
        this.isDetectingPose = false;
    },

    playAudioChime() {
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, audioCtx.currentTime);
            gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.35);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.35);
        } catch(e) {}
    },

    analyzeFaceCentroidAndMass(imgData) {
        const data = imgData.data;
        const w = imgData.width;
        const h = imgData.height;
        let totalMass = 0;
        let sumX = 0;
        let sumY = 0;
        let leftMass = 0;
        let rightMass = 0;
        let topMass = 0;
        let bottomMass = 0;
        const midX = w / 2;
        const midY = h / 2;

        for (let y = 0; y < h; y += 2) {
            for (let x = 0; x < w; x += 2) {
                const idx = (y * w + x) * 4;
                const r = data[idx];
                const g = data[idx + 1];
                const b = data[idx + 2];

                // Skin Chrominance Filter (RGB Rule of Thumb)
                const isSkin = (r > 60 && g > 40 && b > 20 && (r - g) > 10 && (r - b) > 10 && (Math.max(r,g,b) - Math.min(r,g,b)) > 15);
                if (isSkin) {
                    totalMass++;
                    sumX += x;
                    sumY += y;
                    if (x < midX) leftMass++; else rightMass++;
                    if (y < midY) topMass++; else bottomMass++;
                }
            }
        }

        const totalSampledPixels = (w * h) / 4;
        const faceCoveragePercent = (totalMass / totalSampledPixels) * 100;
        const facePresent = faceCoveragePercent >= 1.5 && faceCoveragePercent <= 60;
        const cx = totalMass > 0 ? (sumX / totalMass) / w : 0.5;
        const cy = totalMass > 0 ? (sumY / totalMass) / h : 0.5;
        const hRatio = rightMass > 0 ? (leftMass / rightMass) : 1;
        const vRatio = bottomMass > 0 ? (topMass / bottomMass) : 1;

        return { facePresent, cx, cy, hRatio, vRatio, faceCoveragePercent };
    },

    runPoseLivenessLoop() {
        this.isDetectingPose = true;
        const video = document.getElementById('livenessVideo');
        const offscreen = document.createElement('canvas');
        offscreen.width = 120;
        offscreen.height = 120;
        const ctx = offscreen.getContext('2d', { willReadFrequently: true });

        let currentStepHoldMs = 0;
        let lastTimestamp = performance.now();
        const requiredHoldMs = 800; // 800ms steady frontal hold

        const processFrame = (now) => {
            if (!this.showFaceAttendanceModal || !this.isDetectingPose || !video) return;
            const deltaMs = Math.min(100, now - lastTimestamp);
            lastTimestamp = now;

            if (video.readyState >= 2) {
                ctx.drawImage(video, 0, 0, 120, 120);
                const imgData = ctx.getImageData(0, 0, 120, 120);
                const metrics = this.analyzeFaceCentroidAndMass(imgData);

                if (metrics.facePresent) {
                    this.faceDetected = true;
                    // Frontal Alignment: centered within +/- 14%
                    const isCentered = Math.abs(metrics.cx - 0.5) <= 0.14 && Math.abs(metrics.cy - 0.5) <= 0.14;

                    if (isCentered) {
                        this.faceSteady = true;
                        currentStepHoldMs += deltaMs;
                        this.stabilityProgress = Math.min(100, Math.round((currentStepHoldMs / requiredHoldMs) * 100));
                        this.liveDetectionStatus = '{{ addslashes(__('hrm.face_detected_hold')) }}';
                        this.liveGuideText = '{{ addslashes(__('hrm.guide_hold_straight')) }}';

                        if (currentStepHoldMs >= requiredHoldMs) {
                            this.playAudioChime();
                            this.faceVerifiedSuccess = true;
                            this.isDetectingPose = false;
                            this.captureFinalAttendanceFace(video);
                            return;
                        }
                    } else {
                        this.faceSteady = false;
                        currentStepHoldMs = Math.max(0, currentStepHoldMs - (deltaMs * 0.4));
                        this.stabilityProgress = Math.min(100, Math.round((currentStepHoldMs / requiredHoldMs) * 100));
                        this.liveDetectionStatus = '{{ addslashes(__('hrm.face_center_prompt')) }}';
                        this.liveGuideText = '{{ addslashes(__('hrm.guide_face_center')) }}';
                    }
                } else {
                    this.faceDetected = false;
                    this.faceSteady = false;
                    currentStepHoldMs = Math.max(0, currentStepHoldMs - (deltaMs * 0.6));
                    this.stabilityProgress = 0;
                    this.liveDetectionStatus = '{{ addslashes(__('hrm.searching_face')) }}';
                    this.liveGuideText = '{{ addslashes(__('hrm.guide_face_inside_frame')) }}';
                }
            }

            requestAnimationFrame(processFrame);
        };

        requestAnimationFrame(processFrame);
    },

    captureFinalAttendanceFace(video) {
        const canvas = document.createElement('canvas');
        canvas.width = 640;
        canvas.height = 640;
        const ctx = canvas.getContext('2d');
        
        // Center-crop 1:1 square
        const vw = video.videoWidth || 640;
        const vh = video.videoHeight || 480;
        const minDim = Math.min(vw, vh);
        const sx = (vw - minDim) / 2;
        const sy = (vh - minDim) / 2;
        
        ctx.drawImage(video, sx, sy, minDim, minDim, 0, 0, 640, 640);
        this.faceCapturedFrame = canvas.toDataURL('image/jpeg', 0.9);
        this.stopAttendanceCamera();
        this.submitFaceAttendance();
    },

    submitFaceAttendance() {
        if (!this.faceCapturedFrame) return;
        this.isSubmittingAttendance = true;

        const endpoint = this.attendanceActionType === 'in'
            ? '{{ route('hrm.attendance.clock-in') }}'
            : '{{ route('hrm.attendance.clock-out') }}';

        const payload = {
            _token: '{{ csrf_token() }}',
            latitude: this.currentLat,
            longitude: this.currentLng,
            accuracy: this.currentAccuracy || 10,
            location_id: '{{ $primaryLocation?->id }}',
            notes: this.attendanceNotes,
            photo: this.faceCapturedFrame,
            face_data: this.faceCapturedFrame
        };

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            this.isSubmittingAttendance = false;
            if (data.success || data.attendance) {
                this.showToast(data.message || '{{ addslashes(__('hrm.attendance_recorded_success')) }}', 'success');
                this.showFaceAttendanceModal = false;
                
                // Real-time Event Mutation (Zero Full-Page Reload)
                if (window.CoocaBus) {
                    window.CoocaBus.emitDataMutated('attendance', data);
                }
            } else {
                this.attendanceErrorMessage = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : '{{ addslashes(__('hrm.attendance_verify_failed')) }}');
            }
        })
        .catch(err => {
            this.isSubmittingAttendance = false;
            this.attendanceErrorMessage = '{{ addslashes(__('hrm.network_or_server_error')) }}' + err.message;
        });
    },

    // Location Management Helpers
    openEditLocation(loc, updateUrl) {
        this.selectedLocation = loc;
        this.locationEditAction = updateUrl;
        this.showEditLocationModal = true;
    },

    fillGpsToLocationForm(formType = 'add') {
        if (!navigator.geolocation) {
            const gpsErr = '{{ addslashes(__('hrm.gps_unsupported')) }}';
            if (window.showAppToast) {
                window.showAppToast('error', gpsErr);
            } else {
                alert(gpsErr);
            }
            return;
        }
        navigator.geolocation.getCurrentPosition(pos => {
            const lat = pos.coords.latitude.toFixed(6);
            const lng = pos.coords.longitude.toFixed(6);
            if (formType === 'add') {
                const latInput = document.getElementById('add_loc_lat');
                const lngInput = document.getElementById('add_loc_lng');
                if (latInput) latInput.value = lat;
                if (lngInput) lngInput.value = lng;
            } else if (formType === 'edit' && this.selectedLocation) {
                this.selectedLocation.latitude = lat;
                this.selectedLocation.longitude = lng;
            }
        }, err => {
            const errMsg = '{{ addslashes(__('hrm.gps_failed')) }}' + err.message;
            if (window.showAppToast) {
                window.showAppToast('error', errMsg);
            } else {
                alert(errMsg);
            }
        }, { enableHighAccuracy: true });
    }
}">

    <!-- ======================================================== -->
    <!-- 1. BENTO EXECUTIVE STATS HERO                           -->
    <!-- ======================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        <!-- Card 1: Total Staf & Biometrik Wajah -->
        <div class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_2px_10px_rgba(0,0,0,0.03)] space-y-2">
            <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                <span class="text-[12px] font-bold uppercase tracking-wider">{{ __('hrm.stats_total_staff_active') }}</span>
                <div class="w-8 h-8 rounded-[9px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="scan-face" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-[26px] font-extrabold text-black dark:text-white tracking-tight tabular-nums">
                {{ number_format($totalStaff) }} <span class="text-[14px] font-semibold text-black/50 dark:text-white/50">{{ __('hrm.people_unit') }}</span>
            </div>
            <p class="text-[11.5px] text-[#34C759] font-semibold flex items-center gap-1">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                <span>{{ __('hrm.stats_face_enrolled_count', ['count' => $totalFaceEnrolled]) }}</span>
            </p>
        </div>

        <!-- Card 2: Estimasi Gaji Pokok & BPJS -->
        <div class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_2px_10px_rgba(0,0,0,0.03)] space-y-2">
            <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                <span class="text-[12px] font-bold uppercase tracking-wider">{{ __('hrm.stats_base_salary_bpjs') }}</span>
                <div class="w-8 h-8 rounded-[9px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                    <i data-lucide="shield" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-[22px] sm:text-[24px] font-extrabold text-black dark:text-white tracking-tight tabular-nums truncate">
                Rp {{ number_format($totalBaseSalary, 0, ',', '.') }}
            </div>
            <p class="text-[11.5px] text-black/60 dark:text-white/60 font-medium">
                {{ __('hrm.stats_bpjs_summary', ['tk' => $totalBpjsTk, 'kes' => $totalBpjsKes]) }}
            </p>
        </div>

        <!-- Card 3: Saldo Kasbon Aktif -->
        <div class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_2px_10px_rgba(0,0,0,0.03)] space-y-2">
            <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                <span class="text-[12px] font-bold uppercase tracking-wider">{{ __('hrm.stats_active_loans_balance') }}</span>
                <div class="w-8 h-8 rounded-[9px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-[22px] sm:text-[24px] font-extrabold text-black dark:text-white tracking-tight tabular-nums truncate">
                Rp {{ number_format($totalActiveLoans, 0, ',', '.') }}
            </div>
            <p class="text-[11.5px] text-black/55 dark:text-white/55 font-medium">{{ __('hrm.stats_loan_deduction_note') }}</p>
        </div>

        <!-- Card 4: Penggajian Terakhir -->
        <div class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_2px_10px_rgba(0,0,0,0.03)] space-y-2">
            <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                <span class="text-[12px] font-bold uppercase tracking-wider">{{ __('hrm.stats_latest_payroll') }}</span>
                <div class="w-8 h-8 rounded-[9px] bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight truncate">
                {{ $latestPaidPayroll ? $latestPaidPayroll->formatted_period : __('hrm.none_yet') }}
            </div>
            <p class="text-[11.5px] text-black/55 dark:text-white/55 font-medium">
                {{ $latestPaidPayroll ? __('hrm.stats_paid_thp', ['amount' => number_format((float)$latestPaidPayroll->total_take_home_pay, 0, ',', '.')]) : __('hrm.stats_create_first_payroll') }}
            </p>
        </div>
    </div>

    <!-- Alpine Reactive Toast Notification Banner -->
    <div x-show="toast.show" x-transition.opacity.duration.300ms
        class="fixed top-6 right-6 z-50 max-w-md p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_20px_40px_rgba(0,0,0,0.2)] flex items-center gap-3 backdrop-blur-xl"
        style="display: none;">
        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
            :class="toast.type === 'success' ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-[#FF3B30]/15 text-[#FF3B30]'">
            <template x-if="toast.type === 'success'">
                <i data-lucide="check-circle" class="w-4.5 h-4.5"></i>
            </template>
            <template x-if="toast.type !== 'success'">
                <i data-lucide="alert-circle" class="w-4.5 h-4.5"></i>
            </template>
        </div>
        <p class="text-[13px] font-semibold text-black dark:text-white" x-text="toast.message"></p>
        <button type="button" @click="toast.show = false" class="ml-auto text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white cursor-pointer">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- 2. SECTION HEADER & ACTION TOOLBAR (APPLE HIG / LINEAR)   -->
    <!-- ======================================================== -->
    <div class="space-y-3 border-b border-black/10 dark:border-white/10 pb-4">
        {{-- Mobile Subnav Selector (< lg viewports where desktop secondary sidebar is hidden) --}}
        <div class="lg:hidden w-full flex items-center justify-between gap-2.5 p-2 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-xs">
            <label for="mobile-hrm-tab-select" class="text-[12px] font-bold text-black/60 dark:text-white/60 flex items-center gap-1.5 shrink-0 pl-1">
                <i data-lucide="layers" class="w-4 h-4 text-[#007AFF]"></i>
                <span class="hidden sm:inline">Navigasi:</span>
            </label>
            <select id="mobile-hrm-tab-select" @change="setTab($event.target.value)" :value="activeTab"
                class="flex-1 bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white font-semibold text-[13px] rounded-[10px] px-3 py-2 border-0 focus:ring-2 focus:ring-[#007AFF] outline-hidden cursor-pointer">
                <option value="employees">👥 {{ __('hrm.tab_employees') }} ({{ $totalStaff }})</option>
                <option value="attendance">📷 {{ __('hrm.tab_attendance') }} ({{ $todayPresentCount }})</option>
                <option value="corrections">📝 {{ __('hrm.tab_corrections') }} ({{ $pendingCorrectionsCount > 0 ? $pendingCorrectionsCount . ' ' . __('hrm.needs_review') : $corrections->total() }})</option>
                <option value="locations">📍 {{ __('hrm.tab_locations') }} ({{ $allLocations->count() }})</option>
                <option value="shifts">⏰ {{ __('hrm.tab_shifts') }} ({{ $workShifts->count() }})</option>
                <option value="schedules">📅 {{ __('hrm.tab_schedules') }} ({{ $employeeSchedules->total() }})</option>
                <option value="payrolls">🧾 {{ __('hrm.tab_payrolls') }} ({{ $payrolls->total() }})</option>
                <option value="loans">💳 {{ __('hrm.tab_loans') }} ({{ $loans->total() }})</option>
            </select>
        </div>

        {{-- Desktop Section Title & Dynamic Context Action Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            {{-- Contextual Section Title & Description --}}
            <div class="flex items-center gap-3 min-w-0">
                <template x-if="activeTab === 'employees'">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                            <i data-lucide="users-2" class="w-4.5 h-4.5" stroke-width="2"></i>
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('hrm.tab_employees') }}</h2>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF]">{{ $totalStaff }} Staf</span>
                            </div>
                            <p class="text-[12px] text-black/55 dark:text-white/55 hidden sm:block">Kelola profil anggota tim, struktur upah, status kepegawaian, dan biometrik wajah.</p>
                        </div>
                    </div>
                </template>

                <template x-if="activeTab === 'attendance'">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] dark:bg-[#34C759]/20 dark:text-[#30D158] flex items-center justify-center shrink-0">
                            <i data-lucide="scan-face" class="w-4.5 h-4.5" stroke-width="2"></i>
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('hrm.tab_attendance') }}</h2>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/10 text-[#34C759] dark:bg-[#30D158]/20 dark:text-[#30D158]">{{ $todayPresentCount }} Hadir Hari Ini</span>
                            </div>
                            <p class="text-[12px] text-black/55 dark:text-white/55 hidden sm:block">Log presensi wajah harian, status kehadiran, jam clock-in/out, dan kepatuhan radius.</p>
                        </div>
                    </div>
                </template>

                <template x-if="activeTab === 'corrections'">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] dark:bg-[#FF9500]/20 dark:text-[#FF9F0A] flex items-center justify-center shrink-0">
                            <i data-lucide="file-check-2" class="w-4.5 h-4.5" stroke-width="2"></i>
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('hrm.tab_corrections') }}</h2>
                                @if($pendingCorrectionsCount > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30] text-white animate-pulse">{{ $pendingCorrectionsCount }} Perlu Review</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70">{{ $corrections->total() }} Pengajuan</span>
                                @endif
                            </div>
                            <p class="text-[12px] text-black/55 dark:text-white/55 hidden sm:block">Daftar permohonan koreksi jam presensi yang diajukan staf beserta alasan dan bukti foto.</p>
                        </div>
                    </div>
                </template>

                <template x-if="activeTab === 'locations'">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-[10px] bg-[#FF2D55]/10 text-[#FF2D55] dark:bg-[#FF2D55]/20 dark:text-[#FF375F] flex items-center justify-center shrink-0">
                            <i data-lucide="map-pin" class="w-4.5 h-4.5" stroke-width="2"></i>
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('hrm.tab_locations') }}</h2>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF2D55]/10 text-[#FF2D55] dark:bg-[#FF375F]/20 dark:text-[#FF375F]">{{ $allLocations->count() }} Titik Presensi</span>
                            </div>
                            <p class="text-[12px] text-black/55 dark:text-white/55 hidden sm:block">Pengaturan titik koordinat kantor/cabang dan radius geofence GPS untuk clock-in staf.</p>
                        </div>
                    </div>
                </template>

                <template x-if="activeTab === 'shifts'">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-[10px] bg-[#30B0C7]/10 text-[#30B0C7] dark:bg-[#30B0C7]/20 dark:text-[#40C8E0] flex items-center justify-center shrink-0">
                            <i data-lucide="clock" class="w-4.5 h-4.5" stroke-width="2"></i>
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('hrm.tab_shifts') }}</h2>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#30B0C7]/10 text-[#30B0C7] dark:bg-[#40C8E0]/20 dark:text-[#40C8E0]">{{ $workShifts->count() }} Shift Terdaftar</span>
                            </div>
                            <p class="text-[12px] text-black/55 dark:text-white/55 hidden sm:block">Atur jam kerja masuk, jam pulang, toleransi keterlambatan, dan jam istirahat staf.</p>
                        </div>
                    </div>
                </template>

                <template x-if="activeTab === 'schedules'">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-[10px] bg-[#5856D6]/10 text-[#5856D6] dark:bg-[#5856D6]/20 dark:text-[#5E5CE6] flex items-center justify-center shrink-0">
                            <i data-lucide="calendar" class="w-4.5 h-4.5" stroke-width="2"></i>
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('hrm.tab_schedules') }}</h2>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#5856D6]/10 text-[#5856D6] dark:bg-[#5E5CE6]/20 dark:text-[#5E5CE6]">{{ $employeeSchedules->total() }} Roster</span>
                            </div>
                            <p class="text-[12px] text-black/55 dark:text-white/55 hidden sm:block">Penugasan shift kerja harian dan roster bergilir per karyawan atau divisi.</p>
                        </div>
                    </div>
                </template>

                <template x-if="activeTab === 'payrolls'">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-[10px] bg-[#AF52DE]/10 text-[#AF52DE] dark:bg-[#AF52DE]/20 dark:text-[#BF5AF2] flex items-center justify-center shrink-0">
                            <i data-lucide="receipt" class="w-4.5 h-4.5" stroke-width="2"></i>
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('hrm.tab_payrolls') }}</h2>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#AF52DE]/10 text-[#AF52DE] dark:bg-[#BF5AF2]/20 dark:text-[#BF5AF2]">{{ $payrolls->total() }} Periode</span>
                            </div>
                            <p class="text-[12px] text-black/55 dark:text-white/55 hidden sm:block">Kalkulasi penggajian bulanan otomatis, potongan BPJS, PPh 21 TER, dan slip gaji digital.</p>
                        </div>
                    </div>
                </template>

                <template x-if="activeTab === 'loans'">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                            <i data-lucide="credit-card" class="w-4.5 h-4.5" stroke-width="2"></i>
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('hrm.tab_loans') }}</h2>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF]">{{ $loans->total() }} Kasbon</span>
                            </div>
                            <p class="text-[12px] text-black/55 dark:text-white/55 hidden sm:block">Pencatatan pinjaman darurat karyawan dan skema potong gaji otomatis.</p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Action Toolbar: Simulator Link + Dynamic Primary CTA --}}
            <div class="flex items-center gap-2 justify-end flex-wrap sm:flex-nowrap shrink-0">
                <!-- Simulator Pajak Button -->
                <a href="{{ route('tax.index') }}"
                    class="h-10 px-3 rounded-[11px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12.5px] font-semibold flex items-center gap-2 transition active:scale-[0.98]">
                    <i data-lucide="scale" class="w-4 h-4 text-[#AF52DE]"></i>
                    <span class="hidden md:inline">{{ __('hrm.tax_simulator_pph21') }}</span>
                    <span class="md:hidden">PPh 21</span>
                </a>

                <!-- Dynamic Actions per Active Tab -->
                <template x-if="activeTab === 'employees'">
                    <div class="flex items-center gap-2">
                        @if (\App\Support\Context::hasPermission('hrm.manage') || \App\Support\Context::hasPermission('users.manage'))
                        <a href="{{ route('hrm.employees.export') }}"
                            class="h-10 px-3 rounded-[11px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[12.5px] font-semibold flex items-center gap-2 transition active:scale-[0.98] cursor-pointer"
                            title="{{ __('hrm.title_export_employees_excel') }}">
                            <i data-lucide="download" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                            <span class="hidden xl:inline">{{ __('hrm.btn_export_excel') }}</span>
                        </a>
                        <button type="button" @click="showImportEmployeeModal = true"
                            class="h-10 px-3 rounded-[11px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[12.5px] font-semibold flex items-center gap-2 transition active:scale-[0.98] cursor-pointer"
                            title="{{ __('hrm.title_import_employees_excel') }}">
                            <i data-lucide="upload" class="w-4 h-4 text-[#007AFF]"></i>
                            <span class="hidden xl:inline">{{ __('hrm.btn_import_employees') }}</span>
                        </button>
                        <button type="button" @click="showAddEmployeeModal = true"
                            class="h-10 px-3.5 rounded-[11px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(0,122,255,0.3)] transition active:scale-[0.98] cursor-pointer">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>{{ __('hrm.btn_add_employee') }}</span>
                        </button>
                        @endif
                    </div>
                </template>

                <template x-if="activeTab === 'attendance'">
                    <div class="flex items-center gap-2">
                        @if (\App\Support\Context::hasPermission('hrm.attendance.manage') || \App\Support\Context::hasPermission('hrm.manage') || \App\Support\Context::hasPermission('users.manage'))
                        <a href="{{ route('hrm.attendance.export') }}"
                            class="h-10 px-3 rounded-[11px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[12.5px] font-semibold flex items-center gap-2 transition active:scale-[0.98] cursor-pointer"
                            title="{{ __('hrm.title_export_attendance_excel') }}">
                            <i data-lucide="download" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                            <span class="hidden xl:inline">{{ __('hrm.btn_export_attendance') }}</span>
                        </a>
                        <button type="button" @click="showImportAttendanceModal = true"
                            class="h-10 px-3 rounded-[11px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[12.5px] font-semibold flex items-center gap-2 transition active:scale-[0.98] cursor-pointer"
                            title="{{ __('hrm.title_import_fingerprint') }}">
                            <i data-lucide="fingerprint" class="w-4 h-4 text-[#34C759]"></i>
                            <span class="hidden xl:inline">{{ __('hrm.btn_import_fingerprint') }}</span>
                        </button>
                        @endif
                        <button type="button" @click="startFaceAttendance('in')"
                            class="h-10 px-3.5 rounded-[11px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(52,199,89,0.3)] transition active:scale-[0.98] cursor-pointer">
                            <i data-lucide="scan-face" class="w-4 h-4"></i>
                            <span>{{ __('hrm.btn_face_attendance') }}</span>
                        </button>
                    </div>
                </template>

                <template x-if="activeTab === 'corrections'">
                    <button type="button" @click="showAddCorrectionModal = true"
                        class="h-10 px-3.5 rounded-[11px] bg-[#FF9500] hover:bg-[#E08500] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(255,149,0,0.3)] transition active:scale-[0.98] cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>{{ __('hrm.btn_apply_correction') }}</span>
                    </button>
                </template>

                <template x-if="activeTab === 'locations'">
                    @if (\App\Support\Context::hasPermission('hrm.manage') || \App\Support\Context::hasPermission('users.manage'))
                    <button type="button" @click="showAddLocationModal = true"
                        class="h-10 px-3.5 rounded-[11px] bg-[#FF2D55] hover:bg-[#E02648] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(255,45,85,0.3)] transition active:scale-[0.98] cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>{{ __('hrm.btn_add_location') }}</span>
                    </button>
                    @endif
                </template>

                <template x-if="activeTab === 'shifts'">
                    @if (\App\Support\Context::hasPermission('hrm.manage') || \App\Support\Context::hasPermission('users.manage'))
                    <button type="button" @click="showAddShiftModal = true"
                        class="h-10 px-3.5 rounded-[11px] bg-[#30B0C7] hover:bg-[#2591a3] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(48,176,199,0.3)] transition active:scale-[0.98] cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>{{ __('hrm.btn_add_shift') }}</span>
                    </button>
                    @endif
                </template>

                <template x-if="activeTab === 'schedules'">
                    @if (\App\Support\Context::hasPermission('hrm.manage') || \App\Support\Context::hasPermission('users.manage'))
                    <button type="button" @click="showAddScheduleModal = true"
                        class="h-10 px-3.5 rounded-[11px] bg-[#5856D6] hover:bg-[#4745B8] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(88,86,214,0.3)] transition active:scale-[0.98] cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>{{ __('hrm.btn_set_roster_schedule') }}</span>
                    </button>
                    @endif
                </template>

                <template x-if="activeTab === 'payrolls'">
                    @if (\App\Support\Context::hasPermission('hrm.payroll.manage') || \App\Support\Context::hasPermission('users.manage'))
                    <a href="{{ route('hrm.payrolls.create') }}"
                        class="h-10 px-3.5 rounded-[11px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(52,199,89,0.3)] transition active:scale-[0.98] cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>{{ __('hrm.btn_create_payroll') }}</span>
                    </a>
                    @endif
                </template>

                <template x-if="activeTab === 'loans'">
                    @if (\App\Support\Context::hasPermission('hrm.payroll.manage') || \App\Support\Context::hasPermission('hrm.manage') || \App\Support\Context::hasPermission('users.manage'))
                    <button type="button" @click="showAddLoanModal = true"
                        class="h-10 px-3.5 rounded-[11px] bg-[#FF9500] hover:bg-[#E08500] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(255,149,0,0.3)] transition active:scale-[0.98] cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>{{ __('hrm.btn_record_loan') }}</span>
                    </button>
                    @endif
                </template>
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- 3. TAB SUB-PANELS (MODULARIZED)                          --}}
    {{-- ======================================================== --}}
    @include('app.hrm.partials.tab-employees')
    @include('app.hrm.partials.tab-attendance')
    @include('app.hrm.partials.tab-corrections')
    @include('app.hrm.partials.tab-locations')
    @include('app.hrm.partials.tab-shifts')
    @include('app.hrm.partials.tab-schedules')
    @include('app.hrm.partials.tab-payrolls')
    @include('app.hrm.partials.tab-loans')

    {{-- ======================================================== --}}
    {{-- 4. TELEPORTED MODAL DIALOGS (MODULARIZED)                --}}
    {{-- ======================================================== --}}
    @include('app.hrm.modals.register-face')
    @include('app.hrm.modals.face-attendance')
    @include('app.hrm.modals.add-location')
    @include('app.hrm.modals.edit-location')
    @include('app.hrm.modals.add-employee')
    @include('app.hrm.modals.edit-employee')
    @include('app.hrm.modals.add-loan')
    @include('app.hrm.modals.add-correction')
    @include('app.hrm.modals.review-correction')
    @include('app.hrm.modals.add-shift')
    @include('app.hrm.modals.edit-shift')
    @include('app.hrm.modals.add-schedule')
    @include('app.hrm.modals.edit-schedule')
    @include('app.hrm.modals.import-employee')
    @include('app.hrm.modals.import-attendance')


</div>
@endsection
