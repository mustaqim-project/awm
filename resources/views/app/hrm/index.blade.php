@extends('layouts.app', [
    'title' => 'SDM & Penggajian (HRM)',
    'headerTitle' => 'Manajemen SDM & Penggajian',
    'headerSubtitle' => 'Kelola profil staf, biometrik wajah anti-spoofing, kepesertaan BPJS, kasbon, geofencing lokasi, dan penggajian otomatis.'
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
    <!-- 2. SEGMENTED NAVIGATION CONTROLS                         -->
    <!-- ======================================================== -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 border-b border-black/10 dark:border-white/10 pb-3">
        <div class="inline-flex p-1.5 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] backdrop-blur-md overflow-x-auto max-w-full">
            <button type="button" @click="setTab('employees')"
                :class="activeTab === 'employees' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white font-semibold'"
                class="px-3.5 py-2 min-h-[44px] sm:min-h-[38px] rounded-[10px] text-[13px] transition-all flex items-center gap-2 cursor-pointer whitespace-nowrap active:scale-[0.98]">
                <i data-lucide="users" class="w-4 h-4 text-[#007AFF]"></i>
                <span>{{ __('hrm.tab_employees') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10.5px] bg-black/5 dark:bg-white/10 font-bold">{{ $totalStaff }}</span>
            </button>

            <button type="button" @click="setTab('attendance')"
                :class="activeTab === 'attendance' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white font-semibold'"
                class="px-3.5 py-2 min-h-[44px] sm:min-h-[38px] rounded-[10px] text-[13px] transition-all flex items-center gap-2 cursor-pointer whitespace-nowrap active:scale-[0.98]">
                <i data-lucide="scan-face" class="w-4 h-4 text-[#34C759]"></i>
                <span>{{ __('hrm.tab_attendance') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10.5px] bg-[#34C759]/15 text-[#34C759] font-bold">{{ $todayPresentCount }}</span>
            </button>

            <button type="button" @click="setTab('corrections')"
                :class="activeTab === 'corrections' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white font-semibold'"
                class="px-3.5 py-2 min-h-[44px] sm:min-h-[38px] rounded-[10px] text-[13px] transition-all flex items-center gap-2 cursor-pointer whitespace-nowrap active:scale-[0.98]">
                <i data-lucide="file-check-2" class="w-4 h-4 text-[#FF9500]"></i>
                <span>{{ __('hrm.tab_corrections') }}</span>
                @if($pendingCorrectionsCount > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10.5px] bg-[#FF3B30] text-white font-bold animate-pulse">{{ $pendingCorrectionsCount }}</span>
                @else
                    <span class="px-1.5 py-0.2 rounded-full text-[10.5px] bg-black/5 dark:bg-white/10 font-bold">{{ $corrections->total() }}</span>
                @endif
            </button>

            <button type="button" @click="setTab('locations')"
                :class="activeTab === 'locations' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white font-semibold'"
                class="px-3.5 py-2 min-h-[44px] sm:min-h-[38px] rounded-[10px] text-[13px] transition-all flex items-center gap-2 cursor-pointer whitespace-nowrap active:scale-[0.98]">
                <i data-lucide="map-pin" class="w-4 h-4 text-[#FF2D55]"></i>
                <span>{{ __('hrm.tab_locations') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10.5px] bg-black/5 dark:bg-white/10 font-bold">{{ $allLocations->count() }}</span>
            </button>

            <button type="button" @click="setTab('shifts')"
                :class="activeTab === 'shifts' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white font-semibold'"
                class="px-3.5 py-2 min-h-[44px] sm:min-h-[38px] rounded-[10px] text-[13px] transition-all flex items-center gap-2 cursor-pointer whitespace-nowrap active:scale-[0.98]">
                <i data-lucide="clock" class="w-4 h-4 text-[#30B0C7]"></i>
                <span>{{ __('hrm.tab_shifts') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10.5px] bg-black/5 dark:bg-white/10 font-bold">{{ $workShifts->count() }}</span>
            </button>

            <button type="button" @click="setTab('schedules')"
                :class="activeTab === 'schedules' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white font-semibold'"
                class="px-3.5 py-2 min-h-[44px] sm:min-h-[38px] rounded-[10px] text-[13px] transition-all flex items-center gap-2 cursor-pointer whitespace-nowrap active:scale-[0.98]">
                <i data-lucide="calendar" class="w-4 h-4 text-[#5856D6]"></i>
                <span>{{ __('hrm.tab_schedules') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10.5px] bg-black/5 dark:bg-white/10 font-bold">{{ $employeeSchedules->total() }}</span>
            </button>

            <button type="button" @click="setTab('payrolls')"
                :class="activeTab === 'payrolls' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white font-semibold'"
                class="px-3.5 py-2 min-h-[44px] sm:min-h-[38px] rounded-[10px] text-[13px] transition-all flex items-center gap-2 cursor-pointer whitespace-nowrap active:scale-[0.98]">
                <i data-lucide="receipt" class="w-4 h-4 text-[#AF52DE]"></i>
                <span>{{ __('hrm.tab_payrolls') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10.5px] bg-black/5 dark:bg-white/10 font-bold">{{ $payrolls->total() }}</span>
            </button>

            <button type="button" @click="setTab('loans')"
                :class="activeTab === 'loans' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white font-semibold'"
                class="px-3.5 py-2 min-h-[44px] sm:min-h-[38px] rounded-[10px] text-[13px] transition-all flex items-center gap-2 cursor-pointer whitespace-nowrap active:scale-[0.98]">
                <i data-lucide="credit-card" class="w-4 h-4 text-[#007AFF]"></i>
                <span>{{ __('hrm.tab_loans') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10.5px] bg-black/5 dark:bg-white/10 font-bold">{{ $loans->total() }}</span>
            </button>
        </div>

        <div class="flex items-center gap-2 w-full lg:w-auto justify-between sm:justify-end flex-wrap sm:flex-nowrap">
            <!-- Link ke Simulator Pajak -->
            <a href="{{ route('tax.index') }}"
                class="h-11 sm:h-10 min-h-[44px] sm:min-h-0 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12.5px] font-semibold flex items-center gap-2 transition active:scale-[0.98]">
                <i data-lucide="scale" class="w-4 h-4 text-[#AF52DE]"></i>
                <span>{{ __('hrm.tax_simulator_pph21') }}</span>
            </a>

            <!-- Action CTA Dinamis sesuai Tab -->
            <template x-if="activeTab === 'employees'">
                <div class="flex items-center gap-2">
                    <a href="{{ route('hrm.employees.export') }}"
                        class="h-11 sm:h-10 min-h-[44px] sm:min-h-0 px-3.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold flex items-center gap-2 transition active:scale-[0.98] cursor-pointer"
                        title="{{ __('hrm.title_export_employees_excel') }}">
                        <i data-lucide="download" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                        <span class="hidden sm:inline">{{ __('hrm.btn_export_excel') }}</span>
                    </a>
                    <button type="button" @click="showImportEmployeeModal = true"
                        class="h-11 sm:h-10 min-h-[44px] sm:min-h-0 px-3.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold flex items-center gap-2 transition active:scale-[0.98] cursor-pointer"
                        title="{{ __('hrm.title_import_employees_excel') }}">
                        <i data-lucide="upload" class="w-4 h-4 text-[#007AFF]"></i>
                        <span class="hidden sm:inline">{{ __('hrm.btn_import_employees') }}</span>
                    </button>
                    <button type="button" @click="showAddEmployeeModal = true"
                        class="h-11 sm:h-10 min-h-[44px] sm:min-h-0 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(0,122,255,0.3)] transition active:scale-[0.98] cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>{{ __('hrm.btn_add_employee') }}</span>
                    </button>
                </div>
            </template>

            <template x-if="activeTab === 'attendance'">
                <div class="flex items-center gap-2">
                    <a href="{{ route('hrm.attendance.export') }}"
                        class="h-11 sm:h-10 min-h-[44px] sm:min-h-0 px-3.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold flex items-center gap-2 transition active:scale-[0.98] cursor-pointer"
                        title="{{ __('hrm.title_export_attendance_excel') }}">
                        <i data-lucide="download" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                        <span class="hidden sm:inline">{{ __('hrm.btn_export_attendance') }}</span>
                    </a>
                    <button type="button" @click="showImportAttendanceModal = true"
                        class="h-11 sm:h-10 min-h-[44px] sm:min-h-0 px-3.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold flex items-center gap-2 transition active:scale-[0.98] cursor-pointer"
                        title="{{ __('hrm.title_import_fingerprint') }}">
                        <i data-lucide="fingerprint" class="w-4 h-4 text-[#34C759]"></i>
                        <span class="hidden sm:inline">{{ __('hrm.btn_import_fingerprint') }}</span>
                    </button>
                    <button type="button" @click="startFaceAttendance('in')"
                        class="h-11 sm:h-10 min-h-[44px] sm:min-h-0 px-4 rounded-[12px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(52,199,89,0.3)] transition active:scale-[0.98] cursor-pointer">
                        <i data-lucide="scan-face" class="w-4 h-4"></i>
                        <span>{{ __('hrm.btn_face_attendance') }}</span>
                    </button>
                </div>
            </template>

            <template x-if="activeTab === 'corrections'">
                <button type="button" @click="showAddCorrectionModal = true"
                    class="h-11 sm:h-10 min-h-[44px] sm:min-h-0 px-4 rounded-[12px] bg-[#FF9500] hover:bg-[#E08500] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(255,149,0,0.3)] transition active:scale-[0.98] cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('hrm.btn_apply_correction') }}</span>
                </button>
            </template>

            <template x-if="activeTab === 'locations'">
                <button type="button" @click="showAddLocationModal = true"
                    class="h-11 sm:h-10 min-h-[44px] sm:min-h-0 px-4 rounded-[12px] bg-[#FF2D55] hover:bg-[#E02648] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(255,45,85,0.3)] transition active:scale-[0.98] cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('hrm.btn_add_location') }}</span>
                </button>
            </template>

            <template x-if="activeTab === 'shifts'">
                <button type="button" @click="showAddShiftModal = true"
                    class="h-11 sm:h-10 min-h-[44px] sm:min-h-0 px-4 rounded-[12px] bg-[#30B0C7] hover:bg-[#2591a3] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(48,176,199,0.3)] transition active:scale-[0.98] cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('hrm.btn_add_shift') }}</span>
                </button>
            </template>

            <template x-if="activeTab === 'schedules'">
                <button type="button" @click="showAddScheduleModal = true"
                    class="h-11 sm:h-10 min-h-[44px] sm:min-h-0 px-4 rounded-[12px] bg-[#5856D6] hover:bg-[#4745B8] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(88,86,214,0.3)] transition active:scale-[0.98] cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('hrm.btn_set_roster_schedule') }}</span>
                </button>
            </template>

            <template x-if="activeTab === 'payrolls'">
                <a href="{{ route('hrm.payrolls.create') }}"
                    class="h-11 sm:h-10 min-h-[44px] sm:min-h-0 px-4 rounded-[12px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(52,199,89,0.3)] transition active:scale-[0.98] cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('hrm.btn_create_payroll') }}</span>
                </a>
            </template>

            <template x-if="activeTab === 'loans'">
                <button type="button" @click="showAddLoanModal = true"
                    class="h-11 sm:h-10 min-h-[44px] sm:min-h-0 px-4 rounded-[12px] bg-[#FF9500] hover:bg-[#E08500] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(255,149,0,0.3)] transition active:scale-[0.98] cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('hrm.btn_record_loan') }}</span>
                </button>
            </template>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: KARYAWAN & PROFIL GAJI                            -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'employees'" class="space-y-4" x-transition.opacity>
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <!-- Desktop Table View (>= 768px) -->
            <div x-data="coocaTableSort()" class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse min-w-[1050px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            <x-table-sort-th :col="0" type="string" :title="__('hrm.th_employee_identity')" class="sm:px-5" />
                            <x-table-sort-th :col="1" type="string" :title="__('hrm.th_job_role')" />
                            <x-table-sort-th :col="2" type="string" :title="__('hrm.th_employment_type')" />
                            <x-table-sort-th :col="3" type="number" :title="__('hrm.th_wage_structure')" />
                            <x-table-sort-th :col="4" type="string" :title="__('hrm.th_bpjs_social')" />
                            <x-table-sort-th :col="5" type="string" :title="__('hrm.th_biometric_face')" />
                            <x-table-sort-th :col="6" type="string" :title="__('hrm.th_bank_account')" />
                            <th class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('hrm.th_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($memberships as $m)
                            @php
                                $u = $m->user;
                                $isOwner = ($m->role === 'owner');
                                $hasFace = $m->hasFaceRegistered();
                                $salaryVal = (float)($m->employment_type === 'daily_worker' ? $m->daily_rate : $m->base_salary);
                            @endphp
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors group">
                                <!-- Karyawan -->
                                <td data-sort-value="{{ strtolower($u->name ?? 'User') }}" class="py-3.5 px-4 sm:px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full {{ $hasFace ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-[#007AFF]/12 text-[#007AFF]' }} font-bold text-xs flex items-center justify-center shrink-0">
                                            {{ substr($u->name ?? 'K', 0, 2) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-black dark:text-white truncate text-[13.5px]">
                                                {{ $u->name ?? 'User' }}
                                                @if($isOwner)
                                                    <span class="ml-1 text-[10px] px-2 py-0.5 rounded-full bg-[#FF9500]/15 text-[#FF9500] font-bold">{{ __('hrm.status_owner') }}</span>
                                                @endif
                                            </div>
                                            <div class="text-[11.5px] text-black/55 dark:text-white/55 truncate">
                                                {{ $u->email ?? '-' }}
                                            </div>
                                            <div class="text-[11px] text-black/45 dark:text-white/45 flex flex-wrap items-center gap-2 mt-0.5">
                                                @if($m->nik_ktp)
                                                    <span>{{ __('hrm.nik_prefix') }} <strong class="font-mono text-black/70 dark:text-white/70">{{ $m->nik_ktp }}</strong></span>
                                                @endif
                                                @if($m->whatsapp_number)
                                                    <span class="text-[#25D366] font-medium flex items-center gap-0.5">
                                                        <i data-lucide="phone" class="w-3 h-3"></i>
                                                        {{ $m->whatsapp_number }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Jabatan & Peran -->
                                <td data-sort-value="{{ strtolower($m->job_title ?: ucfirst($m->role)) }}" class="py-3.5 px-3">
                                    <div class="font-semibold text-black dark:text-white">{{ $m->job_title ?: ucfirst($m->role) }}</div>
                                    <div class="text-[11.5px] text-black/50 dark:text-white/50">{{ $m->customRole?->name ?? ucfirst($m->role) }}</div>
                                </td>

                                <!-- Tipe Kerja -->
                                <td data-sort-value="{{ $m->employment_type }}" class="py-3.5 px-3">
                                    @if($m->employment_type === 'daily_worker')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500]">
                                            {{ __('hrm.daily_worker_badge') }}
                                        </span>
                                    @elseif($m->employment_type === 'contract')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#AF52DE]/15 text-[#AF52DE]">
                                            {{ __('portal.employment_type_contract') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                            {{ __('portal.employment_type_permanent') }}
                                        </span>
                                    @endif
                                    @if($m->join_date)
                                        <div class="text-[11px] text-black/50 dark:text-white/50 mt-1 font-medium">
                                            Masuk: {{ \Carbon\Carbon::parse($m->join_date)->translatedFormat('d M Y') }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Struktur Upah -->
                                <td data-sort-value="{{ $salaryVal }}" class="py-3.5 px-3">
                                    @if($m->employment_type === 'daily_worker')
                                        <div class="font-bold text-black dark:text-white tabular-nums">
                                            Rp {{ number_format((float)$m->daily_rate, 0, ',', '.') }} <span class="text-[10.5px] font-normal text-black/50 dark:text-white/50">{{ __('hrm.per_day_suffix') }}</span>
                                        </div>
                                    @else
                                        <div class="font-bold text-black dark:text-white tabular-nums">
                                            Rp {{ number_format((float)$m->base_salary, 0, ',', '.') }}
                                        </div>
                                        @if($m->fixed_allowances > 0 || $m->variable_allowances > 0)
                                            <div class="text-[11.5px] text-black/60 dark:text-white/60 tabular-nums font-medium">
                                                + Tunj: Rp {{ number_format((float)($m->fixed_allowances + $m->variable_allowances), 0, ',', '.') }}
                                            </div>
                                        @endif
                                    @endif
                                </td>

                                <!-- BPJS & Tanggungan -->
                                <td data-sort-value="{{ $m->tax_ptkp_status ?: 'TK/0' }}" class="py-3.5 px-3">
                                    <div class="space-y-1">
                                        <div class="flex flex-wrap gap-1 items-center">
                                            <span class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-black/5 dark:bg-white/10 text-black/80 dark:text-white/80">
                                                {{ $m->tax_ptkp_status ?: 'TK/0' }}
                                            </span>
                                            @if($m->bpjs_tk_enabled)
                                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-[#007AFF]/12 text-[#007AFF]" title="{{ $m->bpjs_tk_number ? 'No: '.$m->bpjs_tk_number : __('portal.active') }}">
                                                    BPJS TK
                                                </span>
                                            @endif
                                            @if($m->bpjs_kes_enabled)
                                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-[#34C759]/12 text-[#34C759]" title="{{ $m->bpjs_kes_number ? 'No: '.$m->bpjs_kes_number : __('portal.active') }}">
                                                    BPJS Kes
                                                </span>
                                            @endif
                                        </div>
                                        @if($m->bpjs_dependents_count > 0)
                                            <div class="text-[11px] text-black/60 dark:text-white/60 font-medium">
                                                {{ __('portal.dependents_label') }} <strong class="text-[#007AFF]">{{ $m->bpjs_dependents_count }}</strong>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Status Biometrik Wajah -->
                                <td data-sort-value="{{ $hasFace ? '1_enrolled' : '0_unenrolled' }}" class="py-3.5 px-3">
                                    @if($hasFace)
                                        <button type="button"
                                            @click="openFaceRegister({{ Js::from($m) }}, '{{ route('hrm.employees.face-register', $m->id) }}')"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/30 hover:bg-[#34C759]/25 transition cursor-pointer"
                                            title="{{ __('hrm.tooltip_manage_face') }}">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('hrm.enrolled') }}</span>
                                        </button>
                                    @else
                                        <button type="button"
                                            @click="openFaceRegister({{ Js::from($m) }}, '{{ route('hrm.employees.face-register', $m->id) }}')"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500] hover:bg-[#FF9500]/25 transition cursor-pointer"
                                            title="{{ __('hrm.tooltip_manage_face') }}">
                                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('hrm.enroll_face') }}</span>
                                        </button>
                                    @endif
                                </td>

                                <!-- Rekening Bank -->
                                <td data-sort-value="{{ strtolower($m->bank_name ?: ($m->bank_account_number ? 'bank' : 'tunai')) }}" class="py-3.5 px-3">
                                    @if($m->bank_account_number)
                                        <div class="font-semibold text-black dark:text-white">{{ $m->bank_name ?: 'Bank' }}</div>
                                        <div class="text-[11.5px] text-black/60 dark:text-white/60 tabular-nums font-mono">{{ $m->bank_account_number }}</div>
                                    @else
                                        <span class="text-[11.5px] text-black/40 dark:text-white/40 font-medium">{{ __('hrm.cash_method') }}</span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button"
                                            @click="openEditEmployee({{ Js::from($m) }}, '{{ route('hrm.employees.update', $m->id) }}')"
                                            class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#007AFF] hover:bg-[#007AFF]/10 dark:hover:bg-[#007AFF]/20 transition-all cursor-pointer shrink-0"
                                            title="{{ __('hrm.btn_edit') }}"
                                            aria-label="{{ __('hrm.btn_edit') }}">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>

                                        @if(! $isOwner)
                                            <form method="POST" action="{{ route('hrm.employees.destroy', $m->id) }}"
                                                onsubmit="return confirm('{{ addslashes(__('hrm.confirm_delete_employee')) }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#FF3B30] hover:bg-[#FF3B30]/10 dark:hover:bg-[#FF3B30]/20 transition-all cursor-pointer shrink-0"
                                                    title="{{ __('hrm.tooltip_delete_employee') }}"
                                                    aria-label="{{ __('hrm.tooltip_delete_employee') }}">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-black/50 dark:text-white/50 font-medium">
                                    {{ __('hrm.empty_staff_list') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Bento Card View (< 768px) -->
            <div class="md:hidden space-y-3.5 p-3.5 sm:p-4">
                @forelse($memberships as $m)
                    @php
                        $u = $m->user;
                        $isOwner = ($m->role === 'owner');
                        $hasFace = $m->hasFaceRegistered();
                    @endphp
                    <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/8 dark:border-white/10 space-y-3.5 shadow-xs">
                        <!-- Top: Profile Header & Badge -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-11 h-11 rounded-full {{ $hasFace ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-[#007AFF]/12 text-[#007AFF]' }} font-bold text-sm flex items-center justify-center shrink-0">
                                    {{ substr($u->name ?? 'K', 0, 2) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-black dark:text-white truncate text-[14px]">
                                        {{ $u->name ?? 'User' }}
                                    </div>
                                    <div class="text-[12px] text-black/60 dark:text-white/60 truncate">
                                        {{ $m->job_title ?: ucfirst($m->role) }} • {{ $m->customRole?->name ?? ucfirst($m->role) }}
                                    </div>
                                    <div class="text-[11.5px] text-black/45 dark:text-white/45 truncate">
                                        {{ $u->email ?? '-' }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1 shrink-0">
                                @if($isOwner)
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-[#FF9500]/15 text-[#FF9500] font-bold">Owner</span>
                                @endif
                                @if($m->employment_type === 'daily_worker')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500]">{{ __('hrm.badges.daily') }}</span>
                                @elseif($m->employment_type === 'contract')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#AF52DE]/15 text-[#AF52DE]">{{ __('hrm.badges.contract') }}</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#34C759]">{{ __('hrm.badges.permanent') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Mid: 2-Col Data Chips -->
                        <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-black/5 dark:border-white/5">
                            <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 space-y-0.5">
                                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('hrm.table.salary_compensation') }}</span>
                                <div class="font-bold text-black dark:text-white tabular-nums text-[12.5px]">
                                    @if($m->employment_type === 'daily_worker')
                                        Rp {{ number_format((float)$m->daily_rate, 0, ',', '.') }}<span class="text-[10px] font-normal text-black/50">/{{ __('hrm.table.day_short') }}</span>
                                    @else
                                        Rp {{ number_format((float)$m->base_salary, 0, ',', '.') }}
                                    @endif
                                </div>
                                @if($m->fixed_allowances > 0)
                                    <span class="text-[10.5px] text-[#34C759] font-medium tabular-nums block">+{{ __('hrm.table.allowance_short') }} Rp {{ number_format((float)$m->fixed_allowances, 0, ',', '.') }}</span>
                                @endif
                            </div>

                            <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 space-y-0.5">
                                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('hrm.table.bpjs_ptkp') }}</span>
                                <div class="flex items-center gap-1 flex-wrap">
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70">{{ $m->tax_ptkp_status ?: 'TK/0' }}</span>
                                    @if($m->bpjs_tk_enabled)
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-[#007AFF]/15 text-[#007AFF]">TK</span>
                                    @endif
                                    @if($m->bpjs_kes_enabled)
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-[#34C759]/15 text-[#34C759]">Kes</span>
                                    @endif
                                </div>
                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block">{{ __('hrm.table.dependents') }}: {{ $m->bpjs_dependents_count ?: 0 }} {{ __('hrm.table.people') }}</span>
                            </div>

                            <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 space-y-0.5">
                                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('hrm.table.face_biometric') }}</span>
                                @if($hasFace)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#34C759]">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i>
                                        <span>{{ __('hrm.badges.registered') }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#FF9500]">
                                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                        <span>{{ __('hrm.badges.not_registered') }}</span>
                                    </span>
                                @endif
                            </div>

                            <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 space-y-0.5">
                                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('hrm.table.payroll_account') }}</span>
                                <span class="text-[11px] font-semibold text-black dark:text-white truncate block">
                                    {{ $m->bank_account_number ? ($m->bank_name ?: 'Bank') . ' ' . $m->bank_account_number : __('hrm.table.cash') }}
                                </span>
                            </div>
                        </div>

                        <!-- Bottom: Touch-Friendly Action Buttons (>= 44px) -->
                        <div class="flex items-center gap-2 pt-1 border-t border-black/5 dark:border-white/5">
                            <button type="button"
                                @click="openFaceRegister({{ Js::from($m) }}, '{{ route('hrm.employees.face-register', $m->id) }}')"
                                class="flex-1 h-11 px-3 rounded-[12px] text-[12.5px] font-bold {{ $hasFace ? 'text-[#34C759] bg-[#34C759]/10 hover:bg-[#34C759]/20' : 'text-[#FF9500] bg-[#FF9500]/10 hover:bg-[#FF9500]/20' }} active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                <i data-lucide="scan-face" class="w-4 h-4"></i>
                                <span>{{ $hasFace ? __('hrm.actions.change_face') : __('hrm.actions.register_face') }}</span>
                            </button>

                            <button type="button"
                                @click="openEditEmployee({{ Js::from($m) }}, '{{ route('hrm.employees.update', $m->id) }}')"
                                class="flex-1 h-11 px-3 rounded-[12px] text-[12.5px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                <span>{{ __('hrm.actions.edit_profile') }}</span>
                            </button>

                            @if(! $isOwner)
                                <form method="POST" action="{{ route('hrm.employees.destroy', $m->id) }}"
                                    onsubmit="return confirm('{{ addslashes(__('hrm.confirm_delete_employee')) }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="h-11 w-11 rounded-[12px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all flex items-center justify-center cursor-pointer shrink-0"
                                        title="{{ __('hrm.actions.delete_staff') }}"
                                        aria-label="{{ __('hrm.actions.delete_staff') }}">
                                        <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-black/50 dark:text-white/50 font-medium">
                        <i data-lucide="users" class="w-8 h-8 mx-auto mb-1.5 text-black/30 dark:text-white/30"></i>
                        <p class="text-xs">{{ __('hrm.empty.no_staff') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 2: PRESENSI WAJAH & REKAP HARIAN                     -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'attendance'" class="space-y-6" x-transition.opacity style="display: none;">
        <!-- 1. BENTO HERO: WIDGET PRESENSI MANDIRI & BIOMETRIK -->
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-5 sm:p-6 shadow-[0_4px_20px_rgba(0,0,0,0.03)] space-y-5">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-[14px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                        <i data-lucide="scan-face" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-[17px] font-bold text-black dark:text-white">{{ __('hrm.anti_spoofing_face_attendance') }}</h3>
                        <p class="text-[12.5px] text-black/60 dark:text-white/60 font-medium">
                            {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }} • {{ __('hrm.wib_timezone') }}
                        </p>
                    </div>
                </div>

                <!-- Digital Clock Display -->
                <div class="flex items-baseline gap-1 px-4 py-2.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10">
                    <span class="text-[28px] sm:text-[34px] font-extrabold tracking-tight tabular-nums text-black dark:text-white" x-text="liveClock">00:00</span>
                    <span class="text-[16px] sm:text-[18px] font-bold text-black/50 dark:text-white/50 tabular-nums" x-text="':' + liveSeconds">:00</span>
                    <span class="ml-1.5 text-[11px] font-bold text-black/50 dark:text-white/50 uppercase">WIB</span>
                </div>
            </div>

            <!-- Geolocation Radar Status & Policy Indicator -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                <!-- Location Status Card -->
                <div class="p-4.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 md:col-span-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-[#007AFF]"></i>
                            <span class="text-[12.5px] font-bold text-black/80 dark:text-white/80">{{ __('hrm.location_geofence_status') }}</span>
                        </div>
                        <template x-if="gpsStatus === 'ready'">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                                :class="isFreeLocation || (distanceMeters !== null && distanceMeters <= officeRadius) ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-[#FF3B30]/15 text-[#FF3B30]'">
                                <span class="w-2 h-2 rounded-full" :class="isFreeLocation || (distanceMeters !== null && distanceMeters <= officeRadius) ? 'bg-[#34C759]' : 'bg-[#FF3B30]'"></span>
                                <span x-text="isFreeLocation ? '{{ addslashes(__('hrm.geo_free_location')) }}' : (distanceMeters !== null && distanceMeters <= officeRadius ? '{{ addslashes(__('hrm.geo_in_range')) }}' : '{{ addslashes(__('hrm.geo_out_of_range')) }}')"></span>
                            </span>
                        </template>
                        <template x-if="gpsStatus === 'locating'">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/15 text-[#007AFF]">
                                <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>
                                <span>{{ __('hrm.searching_gps') }}</span>
                            </span>
                        </template>
                        <template x-if="gpsStatus === 'error' || gpsStatus === 'denied'">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                <span>{{ __('hrm.gps_disabled') }}</span>
                            </span>
                        </template>
                    </div>

                    <p class="text-[13px] font-bold text-black dark:text-white" x-text="gpsMessage">
                        {{ __('hrm.geo_connecting') }}
                    </p>

                    <div class="text-[11.5px] text-black/60 dark:text-white/60 flex flex-wrap items-center gap-x-3 gap-y-1 font-medium">
                        @if($currentUserMembership && $currentUserMembership->isFreeLocation())
                            <span>{{ __('hrm.policy_label') }} <strong class="text-[#007AFF]">{{ __('hrm.free_location_mode') }}</strong> {{ __('hrm.field_staff_note') }}</span>
                        @else
                            <span>{{ __('hrm.office_label') }} <strong>{{ $primaryLocation?->name ?? __('hrm.default_main_outlet') }}</strong></span>
                            <span>{{ __('hrm.radius_limit_label') }} <strong>{{ $primaryLocation?->geofence_radius_meters ?? 50 }} {{ __('hrm.meters_unit') }}</strong></span>
                        @endif
                        <template x-if="currentAccuracy">
                            <span>{{ __('hrm.sensor_accuracy_label') }} <strong class="tabular-nums" x-text="currentAccuracy + 'm'"></strong></span>
                        </template>
                    </div>
                </div>

                <!-- Anti-Spoofing & Privacy Notice Card -->
                <div class="p-4.5 rounded-[16px] bg-[#007AFF]/[0.04] border border-[#007AFF]/15 flex flex-col justify-between gap-2">
                    <div class="flex items-center gap-2 text-[#007AFF]">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span class="text-[11px] font-bold uppercase tracking-wider">{{ __('hrm.zero_photo_storage') }}</span>
                    </div>
                    <p class="text-[11.5px] text-black/70 dark:text-white/70 leading-relaxed font-medium">
                        {{ __('hrm.zero_photo_desc') }}
                    </p>
                </div>
            </div>

            <!-- Action Controls Form -->
            <div class="pt-2">
                @if(!$currentUserAttendance || !$currentUserAttendance->clock_in_at)
                    <!-- ACTION CLOCK-IN MASUK -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <input type="text" x-model="attendanceNotes" placeholder="{{ __('hrm.attendance_notes_placeholder') }}"
                            class="flex-1 h-12 px-4 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:ring-2 focus:ring-[#34C759]/50 placeholder:text-black/40 dark:placeholder:text-white/40">

                        <button type="button" @click="startFaceAttendance('in')"
                            class="h-12 px-7 rounded-[12px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-[14px] font-bold flex items-center justify-center gap-2 shadow-[0_4px_16px_rgba(52,199,89,0.35)] transition active:scale-[0.98] cursor-pointer shrink-0">
                            <i data-lucide="scan-face" class="w-5 h-5"></i>
                            <span>{{ __('hrm.clock_in_face_recognition') }}</span>
                        </button>
                    </div>
                @elseif(!$currentUserAttendance->clock_out_at)
                    <!-- ACTION CLOCK-OUT PULANG -->
                    <div class="p-4.5 rounded-[16px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/20 text-[#34C759]">
                                    {{ __('hrm.currently_working') }}
                                </span>
                                <span class="text-[13.5px] font-bold text-black dark:text-white">
                                    Clock-In: {{ $currentUserAttendance->clock_in_at->format('H:i') }} WIB
                                </span>
                            </div>
                            <p class="text-[12px] text-black/70 dark:text-white/70 font-medium">
                                {{ __('hrm.entry_status_label') }} <strong class="{{ $currentUserAttendance->isLate() ? 'text-[#FF9500]' : 'text-[#34C759]' }}">{{ $currentUserAttendance->isLate() ? __('hrm.late_minutes_badge', ['min' => $currentUserAttendance->late_minutes]) : __('hrm.status_on_time') }}</strong>
                                @if($currentUserAttendance->clock_in_distance_meters)
                                    • {{ __('hrm.distance_from_office_note', ['dist' => $currentUserAttendance->clock_in_distance_meters]) }}
                                @endif
                            </p>
                        </div>

                        <button type="button" @click="startFaceAttendance('out')"
                            class="w-full sm:w-auto h-11 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13.5px] font-bold flex items-center justify-center gap-2 shadow-[0_4px_14px_rgba(0,122,255,0.3)] transition active:scale-[0.98] cursor-pointer">
                            <i data-lucide="scan-face" class="w-4.5 h-4.5"></i>
                            <span>{{ __('hrm.clock_out_face_recognition') }}</span>
                        </button>
                    </div>
                @else
                    <!-- SELESAI HARI INI -->
                    <div class="p-4.5 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/25 flex items-center justify-between">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-full bg-[#34C759]/20 text-[#34C759] flex items-center justify-center shrink-0">
                                <i data-lucide="check-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span class="text-[14px] font-bold text-black dark:text-white">{{ __('hrm.workday_completed') }}</span>
                                <p class="text-[12px] text-black/70 dark:text-white/70 font-medium">
                                    {{ __('hrm.work_in_label') }} <strong>{{ $currentUserAttendance->clock_in_at?->format('H:i') }}</strong> •
                                    {{ __('hrm.work_out_label') }} <strong>{{ $currentUserAttendance->clock_out_at?->format('H:i') }}</strong> •
                                    {{ __('hrm.work_duration_label') }} <strong>{{ $currentUserAttendance->formatted_work_duration }}</strong>
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[11.5px] font-bold bg-[#34C759] text-white">
                            {{ __('hrm.completed') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <!-- 2. REKAP KPI ATTENDANCE HARI INI -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
            <div class="p-4.5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 space-y-1 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                <span class="text-[11.5px] font-bold text-black/60 dark:text-white/60 uppercase">{{ __('hrm.metric_today_present') }}</span>
                <div class="text-[22px] font-extrabold text-black dark:text-white tabular-nums">{{ $todayPresentCount }}</div>
                <p class="text-[11.5px] text-[#34C759] font-bold">{{ __('hrm.metric_present_sub') }}</p>
            </div>
            <div class="p-4.5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 space-y-1 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                <span class="text-[11.5px] font-bold text-black/60 dark:text-white/60 uppercase">{{ __('hrm.late_today') ?? 'Terlambat' }}</span>
                <div class="text-[22px] font-extrabold text-[#FF9500] tabular-nums">{{ $todayLateCount }}</div>
                <p class="text-[11.5px] text-black/55 dark:text-white/55 font-medium">{{ __('hrm.metric_late_sub') }}</p>
            </div>
            <div class="p-4.5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 space-y-1 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                <span class="text-[11.5px] font-bold text-black/60 dark:text-white/60 uppercase">{{ __('hrm.metric_free_location') }}</span>
                <div class="text-[22px] font-extrabold text-[#007AFF] tabular-nums">{{ $todayFreeCount }}</div>
                <p class="text-[11.5px] text-black/55 dark:text-white/55 font-medium">{{ __('hrm.metric_field_staff') }}</p>
            </div>
            <div class="p-4.5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 space-y-1 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                <span class="text-[11.5px] font-bold text-black/60 dark:text-white/60 uppercase">{{ __('hrm.metric_pending_corrections') }}</span>
                <div class="text-[22px] font-extrabold {{ $pendingCorrectionsCount > 0 ? 'text-[#FF3B30]' : 'text-black dark:text-white' }} tabular-nums">{{ $pendingCorrectionsCount }}</div>
                <p class="text-[11.5px] text-black/55 dark:text-white/55 font-medium">{{ __('hrm.metric_pending_sub') }}</p>
            </div>
        </div>

        <!-- 3. TABEL LOG REKAP PRESENSI HARIAN -->
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden shadow-[0_2px_12px_rgba(0,0,0,0.03)] space-y-3">
            <form method="GET" action="{{ route('hrm.index') }}" class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex flex-wrap items-center justify-between gap-3">
                <input type="hidden" name="tab" value="attendance">

                <div class="flex flex-wrap items-center gap-3">
                    <div>
                        <label class="block text-[11px] uppercase font-bold text-black/60 dark:text-white/60 mb-1">{{ __('hrm.col_target_date') }}</label>
                        <input type="date" name="att_date" value="{{ $attDate }}"
                            class="h-11 sm:h-9 px-3 rounded-[10px] sm:rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
                    </div>

                    <div>
                        <label class="block text-[11px] uppercase font-bold text-black/60 dark:text-white/60 mb-1">{{ __('hrm.col_requester_staff') }}</label>
                        <select name="att_user_id" class="h-11 sm:h-9 px-3 rounded-[10px] sm:rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
                            <option value="">{{ __('hrm.filter_all_staff') }}</option>
                            @foreach($memberships as $m)
                                @if($m->user)
                                    <option value="{{ $m->user->id }}" {{ $attUserId === $m->user->id ? 'selected' : '' }}>{{ $m->user->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] uppercase font-bold text-black/60 dark:text-white/60 mb-1">{{ __('hrm.attendance_status_label') }}</label>
                        <select name="att_status" class="h-11 sm:h-9 px-3 rounded-[10px] sm:rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
                            <option value="">{{ __('hrm.filter_all_status') }}</option>
                            <option value="present" {{ $attStatus === 'present' ? 'selected' : '' }}>{{ __('hrm.status_on_time') }}</option>
                            <option value="late" {{ $attStatus === 'late' ? 'selected' : '' }}>{{ __('hrm.late_today') ?? 'Terlambat' }}</option>
                            <option value="half_day" {{ $attStatus === 'half_day' ? 'selected' : '' }}>{{ __('hrm.status_half_day') }}</option>
                            <option value="absent" {{ $attStatus === 'absent' ? 'selected' : '' }}>{{ __('hrm.status_absent') }}</option>
                            <option value="leave" {{ $attStatus === 'leave' ? 'selected' : '' }}>{{ __('hrm.status_official_leave') }}</option>
                            <option value="sick" {{ $attStatus === 'sick' ? 'selected' : '' }}>{{ __('hrm.status_sick') }}</option>
                        </select>
                    </div>

                    <div class="flex items-end pt-5">
                        <button type="submit"
                            class="h-11 sm:h-9 px-4.5 rounded-[10px] sm:rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black dark:text-white text-[13.5px] sm:text-[12.5px] font-bold transition cursor-pointer">
                            {{ __('common.filter') }}
                        </button>
                    </div>
                </div>

                <div class="text-[12.5px] text-black/60 dark:text-white/60 font-medium">
                    {{ __('hrm.filter_records_count', ['count' => $attendances->total()]) }}
                </div>
            </form>

            <!-- Desktop Table View (>= 768px) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse min-w-[950px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            <th class="py-3.5 px-4 sm:px-5">{{ __('hrm.col_requester_staff') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_target_date') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_clock_in') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_clock_out') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_duration') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_location_distance') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_status') }}</th>
                            <th class="py-3.5 px-4 text-right">{{ __('hrm.th_biometric_verification') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($attendances as $att)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                                <td class="py-3.5 px-4 sm:px-5 font-bold text-black dark:text-white">
                                    {{ $att->user?->name ?? 'Staf' }}
                                    <div class="text-[11.5px] text-black/50 dark:text-white/50 font-normal">{{ $att->user?->email }}</div>
                                </td>
                                <td class="py-3.5 px-3 text-black/70 dark:text-white/70 tabular-nums font-medium">
                                    {{ $att->date->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums">
                                    @if($att->clock_in_at)
                                        <div class="font-bold text-black dark:text-white">
                                            {{ $att->clock_in_at->setTimezone($att->timezone ?? 'Asia/Jakarta')->format('H:i') }}
                                            <span class="text-[11px] font-semibold text-[#007AFF]">
                                                {{ \App\Support\TimezoneHelper::abbreviation($att->timezone ?? 'Asia/Jakarta') }}
                                            </span>
                                        </div>
                                        @if($att->clock_in_status === 'late' || $att->late_minutes > 0)
                                            <span class="text-[10.5px] text-[#FF3B30] font-bold">Terlambat {{ $att->late_minutes }}m</span>
                                        @elseif($att->early_in_minutes > 0)
                                            <span class="text-[10.5px] text-[#007AFF] font-bold">{{ $att->early_in_minutes }}m lebih awal</span>
                                        @elseif($att->clock_in_status === 'free_location')
                                            <span class="text-[10.5px] text-[#007AFF] font-bold">{{ __('hrm.geo_free_location') }}</span>
                                        @else
                                            <span class="text-[10.5px] text-[#34C759] font-bold">Tepat Waktu</span>
                                        @endif
                                        @if($att->shift_name)
                                            <div class="text-[10.5px] text-black/50 dark:text-white/50 mt-0.5">
                                                <span>{{ $att->shift_name }}</span>
                                                @if($att->scheduled_start_at)
                                                    <span>({{ $att->scheduled_start_at->setTimezone($att->timezone ?? 'Asia/Jakarta')->format('H:i') }})</span>
                                                @endif
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-black/40 dark:text-white/40">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 tabular-nums">
                                    @if($att->clock_out_at)
                                        <div class="font-bold text-black dark:text-white">
                                            {{ $att->clock_out_at->setTimezone($att->timezone ?? 'Asia/Jakarta')->format('H:i') }}
                                            <span class="text-[11px] font-semibold text-[#007AFF]">
                                                {{ \App\Support\TimezoneHelper::abbreviation($att->timezone ?? 'Asia/Jakarta') }}
                                            </span>
                                        </div>
                                        @if($att->early_out_minutes > 0)
                                            <span class="text-[10.5px] text-[#FF9500] font-bold">Pulang awal {{ $att->early_out_minutes }}m</span>
                                        @endif
                                    @else
                                        <span class="text-black/40 dark:text-white/40">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-semibold text-black/80 dark:text-white/80">
                                    {{ $att->formatted_work_duration }}
                                </td>
                                <td class="py-3.5 px-3 text-[12.5px] text-black/70 dark:text-white/70">
                                    @if($att->clock_in_distance_meters !== null)
                                        <span class="tabular-nums font-bold">{{ $att->clock_in_distance_meters }}m</span>
                                        <div class="text-[11px] text-black/50 dark:text-white/50">{{ $att->location?->name ?? __('hrm.branch_default') }}</div>
                                    @elseif($att->clock_in_status === 'free_location')
                                        <span class="text-[#007AFF] font-bold">{{ __('hrm.geo_free_location') }}</span>
                                    @else
                                        <span class="text-black/40 dark:text-white/40">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($att->status === 'present')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                            {{ __('hrm.status_on_time') }}
                                        </span>
                                    @elseif($att->status === 'late')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500]">
                                            {{ __('hrm.late_today') ?? 'Terlambat' }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#AF52DE]/15 text-[#AF52DE]">
                                            {{ ucfirst($att->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if($att->face_verified)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/30" title="{{ __('hrm.tooltip_verified_biometric') }}">
                                            <i data-lucide="scan-face" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('hrm.face_matched_percent', ['score' => round(($att->face_similarity_score ?? 0.9) * 100)]) }}</span>
                                        </span>
                                    @elseif($att->is_corrected)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#AF52DE]/15 text-[#AF52DE]">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('hrm.ticket_approved_badge') }}</span>
                                        </span>
                                    @else
                                        <span class="text-[11.5px] text-black/50 dark:text-white/50 font-medium">{{ __('hrm.sensor_gps_badge') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-black/50 dark:text-white/50 font-medium">
                                    {{ __('hrm.empty_attendance_filter') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Bento Card View (< 768px) -->
            <div class="md:hidden space-y-3 p-3.5 sm:p-4">
                @forelse($attendances as $att)
                    @php
                        $isPresent = ($att->status === 'present');
                        $isLate = ($att->status === 'late' || $att->clock_in_status === 'late');
                    @endphp
                    <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/8 dark:border-white/10 space-y-3 shadow-xs">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="font-bold text-black dark:text-white text-[13.5px] truncate">
                                    {{ $att->user?->name ?? 'Staf' }}
                                </div>
                                <div class="text-[11.5px] text-black/50 dark:text-white/50 flex items-center gap-1.5 mt-0.5">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                    <span>{{ $att->date->translatedFormat('d M Y') }}</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold {{ $isPresent ? 'bg-[#34C759]/15 text-[#34C759]' : ($isLate ? 'bg-[#FF9500]/15 text-[#FF9500]' : 'bg-[#AF52DE]/15 text-[#AF52DE]') }}">
                                    {{ ucfirst($att->status) }}
                                </span>
                                @if($att->face_verified)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#34C759]">
                                        <i data-lucide="scan-face" class="w-3 h-3"></i>
                                        <span>{{ __('hrm.face_verified_badge') }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs pt-1 border-t border-black/5 dark:border-white/5">
                            <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 space-y-0.5">
                                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('hrm.th_clock_in') }}</span>
                                <div class="font-bold text-black dark:text-white tabular-nums text-[12.5px]">
                                    {{ $att->clock_in_at ? $att->clock_in_at->format('H:i') . ' WIB' : '-' }}
                                </div>
                                @if($att->clock_in_status === 'late')
                                    <span class="text-[10.5px] text-[#FF9500] font-bold block">{{ __('hrm.late_with_min', ['min' => $att->late_minutes]) }}</span>
                                @endif
                            </div>

                            <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 space-y-0.5">
                                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('hrm.th_clock_out') }}</span>
                                <div class="font-bold text-black dark:text-white tabular-nums text-[12.5px]">
                                    {{ $att->clock_out_at ? $att->clock_out_at->format('H:i') . ' WIB' : '-' }}
                                </div>
                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block">{{ __('hrm.th_duration') }}: {{ $att->formatted_work_duration }}</span>
                            </div>
                        </div>

                        @if($att->clock_in_distance_meters !== null || $att->clock_in_status === 'free_location')
                            <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 flex items-center justify-between text-xs">
                                <span class="text-[11px] text-black/50 dark:text-white/50">{{ __('hrm.geofence_radius_label') }}</span>
                                @if($att->clock_in_status === 'free_location')
                                    <span class="text-[11px] font-bold text-[#007AFF]">{{ __('hrm.geo_free_location') }}</span>
                                @else
                                    <span class="font-mono font-bold text-black dark:text-white">{{ $att->clock_in_distance_meters }}m ({{ $att->location?->name ?? __('hrm.branch_default') }})</span>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-8 text-center text-black/50 dark:text-white/50 font-medium">
                        <i data-lucide="calendar-x" class="w-8 h-8 mx-auto mb-1.5 text-black/30 dark:text-white/30"></i>
                        <p class="text-xs">{{ __('hrm.empty_attendance_filter') }}</p>
                    </div>
                @endforelse
            </div>

            @if($attendances->hasPages())
                <div class="p-3.5 border-t border-black/5 dark:border-white/10">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 3: PERSETUJUAN TIKET KOREKSI ABSENSI                 -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'corrections'" class="space-y-4" x-transition.opacity style="display: none;">
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                        <i data-lucide="file-check-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.tickets_approval_title') }}</h3>
                        <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.tickets_approval_sub') }}</p>
                    </div>
                </div>
                <button type="button" @click="showAddCorrectionModal = true"
                    class="h-10 px-4 rounded-[12px] bg-[#FF9500] hover:bg-[#E08500] text-white text-[13px] font-bold flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(255,149,0,0.3)] transition active:scale-[0.98] cursor-pointer shrink-0">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('hrm.btn_apply_correction') }}</span>
                </button>
            </div>

            <!-- Desktop View (>= 768px) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse min-w-[950px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            <th class="py-3.5 px-4 sm:px-5">{{ __('hrm.col_ticket_number') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_requester_staff') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_target_date') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_type_proposed') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_proposed_hours') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_reason_evidence') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_status') }}</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('hrm.col_action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($corrections as $cor)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors group">
                                <td class="py-3.5 px-4 sm:px-5 font-bold text-black dark:text-white tabular-nums">
                                    {{ $cor->correction_number }}
                                </td>
                                <td class="py-3.5 px-3 font-semibold text-black dark:text-white">
                                    {{ $cor->user?->name ?? 'Staf' }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-black/70 dark:text-white/70 font-medium">
                                    {{ $cor->target_date->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-3.5 px-3 text-black/80 dark:text-white/80 font-medium">
                                    {{ $cor->type_label }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-[12.5px]">
                                    @if($cor->proposed_clock_in)
                                        <div>{{ __('hrm.work_in_label') }} <strong>{{ substr((string)$cor->proposed_clock_in, 0, 5) }}</strong></div>
                                    @endif
                                    @if($cor->proposed_clock_out)
                                        <div>{{ __('hrm.work_out_label') }} <strong>{{ substr((string)$cor->proposed_clock_out, 0, 5) }}</strong></div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 max-w-xs truncate text-[12.5px] text-black/70 dark:text-white/70 font-medium" title="{{ $cor->reason }}">
                                    {{ $cor->reason }}
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($cor->status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                            {{ __('hrm.badge_approved') }}
                                        </span>
                                    @elseif($cor->status === 'rejected')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">
                                            {{ __('hrm.badge_rejected') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500]">
                                            {{ __('hrm.badge_pending_review') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end">
                                        <button type="button" @click="openReviewCorrection({{ json_encode($cor) }})"
                                            class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#007AFF] hover:bg-[#007AFF]/10 dark:hover:bg-[#007AFF]/20 transition-all cursor-pointer shrink-0"
                                            title="{{ $cor->status === 'pending' ? __('hrm.review_and_approve') : __('hrm.view_details') }}"
                                            aria-label="{{ $cor->status === 'pending' ? __('hrm.review_and_approve') : __('hrm.view_details') }}">
                                            <i data-lucide="{{ $cor->status === 'pending' ? 'clipboard-check' : 'eye' }}" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-black/50 dark:text-white/50 font-medium">
                                    {{ __('hrm.empty_tickets_list') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Bento Card View (< 768px) -->
            <div class="md:hidden divide-y divide-black/5 dark:divide-white/5">
                @forelse($corrections as $cor)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="font-bold text-black dark:text-white text-[13px] tabular-nums truncate">
                                    {{ $cor->correction_number }}
                                </span>
                                <span class="text-[11px] text-black/40 dark:text-white/40">·</span>
                                <span class="text-[11.5px] text-black/60 dark:text-white/60 font-medium shrink-0">
                                    {{ $cor->target_date->translatedFormat('d M Y') }}
                                </span>
                            </div>
                            @if($cor->status === 'approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#34C759] shrink-0">
                                    {{ __('hrm.badge_approved') }}
                                </span>
                            @elseif($cor->status === 'rejected')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#FF3B30]/15 text-[#FF3B30] shrink-0">
                                    {{ __('hrm.badge_rejected') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#FF9500]/15 text-[#FF9500] shrink-0">
                                    {{ __('hrm.badge_pending_review') }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <div class="font-bold text-[14px] text-black dark:text-white truncate">
                                {{ $cor->user?->name ?? 'Staf' }}
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 shrink-0">
                                {{ $cor->type_label }}
                            </span>
                        </div>

                        <!-- Proposed Hours Bento -->
                        <div class="p-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 grid grid-cols-2 gap-2 text-[12px]">
                            <div>
                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block font-medium">{{ __('hrm.proposed_in') }}</span>
                                <span class="font-bold text-black dark:text-white font-mono">
                                    {{ $cor->proposed_clock_in ? substr((string)$cor->proposed_clock_in, 0, 5) : '-' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block font-medium">{{ __('hrm.proposed_out') }}</span>
                                <span class="font-bold text-black dark:text-white font-mono">
                                    {{ $cor->proposed_clock_out ? substr((string)$cor->proposed_clock_out, 0, 5) : '-' }}
                                </span>
                            </div>
                        </div>

                        @if($cor->reason)
                            <div class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.02] text-[12px] text-black/70 dark:text-white/70">
                                <span class="text-[10.5px] font-semibold text-black/40 dark:text-white/40 block mb-0.5">{{ __('hrm.col_reason_evidence') }}:</span>
                                {{ $cor->reason }}
                            </div>
                        @endif

                        <div class="pt-1">
                            <button type="button" @click="openReviewCorrection({{ json_encode($cor) }})"
                                class="w-full h-11 px-4 rounded-[12px] text-[13px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#0071E3] hover:text-white active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                <i data-lucide="file-check" class="w-4 h-4"></i>
                                <span>{{ $cor->status === 'pending' ? __('hrm.review_and_approve') : __('hrm.view_details') }}</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-black/50 dark:text-white/50 font-medium">
                        <i data-lucide="file-x" class="w-8 h-8 mx-auto mb-1.5 text-black/30 dark:text-white/30"></i>
                        <p class="text-xs">{{ __('hrm.empty_tickets_list') }}</p>
                    </div>
                @endforelse
            </div>

            @if($corrections->hasPages())
                <div class="p-3.5 border-t border-black/5 dark:border-white/10">
                    {{ $corrections->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 4: PENGATURAN LOKASI & GEOFENCE                      -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'locations'" class="space-y-4" x-transition.opacity style="display: none;">
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#FF2D55]/10 text-[#FF2D55] flex items-center justify-center shrink-0">
                        <i data-lucide="map-pin" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.locations_geofence_title') }}</h3>
                        <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.locations_geofence_sub') }}</p>
                    </div>
                </div>
                <button type="button" @click="showAddLocationModal = true"
                    class="h-10 px-4 rounded-[12px] bg-[#FF2D55] hover:bg-[#E02648] text-white text-[13px] font-bold flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(255,45,85,0.3)] transition active:scale-[0.98] cursor-pointer shrink-0">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('hrm.btn_add_location') }}</span>
                </button>
            </div>

            <!-- Desktop View (>= 768px) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse min-w-[850px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            <th class="py-3.5 px-4 sm:px-5">{{ __('hrm.col_location_name') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_address_city') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_gps_coords') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_geofence_radius') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_primary_status') }}</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('hrm.col_action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($allLocations as $loc)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors group">
                                <td class="py-3.5 px-4 sm:px-5 font-bold text-black dark:text-white">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $loc->name }}</span>
                                        @if($loc->is_primary)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF2D55]/15 text-[#FF2D55] border border-[#FF2D55]/30">{{ __('hrm.badge_primary_office') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 text-black/70 dark:text-white/70 text-[12.5px]">
                                    <div>{{ $loc->address ?: __('hrm.address_not_set') }}</div>
                                    <div class="text-[11px] text-black/50 dark:text-white/50">{{ $loc->city ?: __('hrm.field_city') }}</div>
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-mono text-[12px] text-black/80 dark:text-white/80">
                                    @if($loc->latitude && $loc->longitude)
                                        <div>Lat: {{ number_format((float)$loc->latitude, 6) }}</div>
                                        <div>Lng: {{ number_format((float)$loc->longitude, 6) }}</div>
                                    @else
                                        <span class="text-[#FF9500] font-sans text-[11.5px] font-semibold">{{ __('hrm.not_available') }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-bold text-black dark:text-white">
                                    {{ $loc->geofence_radius_meters ?: 50 }} {{ __('hrm.meters_unit') }}
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($loc->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                            {{ __('hrm.status_active') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50">
                                            {{ __('hrm.status_inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button"
                                            @click="openEditLocation({{ Js::from($loc) }}, '{{ route('hrm.locations.update', $loc->id) }}')"
                                            class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#007AFF] hover:bg-[#007AFF]/10 dark:hover:bg-[#007AFF]/20 transition-all cursor-pointer shrink-0"
                                            title="{{ __('hrm.btn_edit') }}"
                                            aria-label="{{ __('hrm.btn_edit') }}">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>

                                        @if(! $loc->is_primary && $allLocations->count() > 1)
                                            <form method="POST" action="{{ route('hrm.locations.destroy', $loc->id) }}"
                                                onsubmit="return confirm('{{ addslashes(__('hrm.confirm_delete_location')) }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#FF3B30] hover:bg-[#FF3B30]/10 dark:hover:bg-[#FF3B30]/20 transition-all cursor-pointer shrink-0"
                                                    title="{{ __('hrm.tooltip_delete_location') }}"
                                                    aria-label="{{ __('hrm.tooltip_delete_location') }}">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-black/50 dark:text-white/50 font-medium">
                                    {{ __('hrm.empty_locations_list') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Bento Card View (< 768px) -->
            <div class="md:hidden divide-y divide-black/5 dark:divide-white/5">
                @forelse($allLocations as $loc)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="font-bold text-black dark:text-white text-[14px] truncate">{{ $loc->name }}</span>
                                @if($loc->is_primary)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF2D55]/15 text-[#FF2D55] border border-[#FF2D55]/30 shrink-0">{{ __('hrm.badge_primary_office') }}</span>
                                @endif
                            </div>
                            @if($loc->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#34C759] shrink-0">
                                    {{ __('hrm.status_active') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50 shrink-0">
                                    {{ __('hrm.status_inactive') }}
                                </span>
                            @endif
                        </div>

                        <div class="text-[12px] text-black/70 dark:text-white/70">
                            <div>{{ $loc->address ?: __('hrm.address_not_set') }}</div>
                            <div class="text-[11px] text-black/50 dark:text-white/50">{{ $loc->city ?: __('hrm.field_city') }}</div>
                        </div>

                        <!-- Coords & Radius Bento -->
                        <div class="p-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 grid grid-cols-2 gap-2 text-[12px]">
                            <div>
                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block font-medium">{{ __('hrm.col_gps_coords') }}</span>
                                @if($loc->latitude && $loc->longitude)
                                    <span class="font-mono text-[11px] font-bold text-black/80 dark:text-white/80 block">
                                        {{ number_format((float)$loc->latitude, 4) }}, {{ number_format((float)$loc->longitude, 4) }}
                                    </span>
                                @else
                                    <span class="text-[#FF9500] text-[11px] font-semibold">{{ __('hrm.not_available') }}</span>
                                @endif
                            </div>
                            <div>
                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block font-medium">{{ __('hrm.col_geofence_radius') }}</span>
                                <span class="font-bold text-black dark:text-white">
                                    {{ $loc->geofence_radius_meters ?: 50 }} {{ __('hrm.meters_unit') }}
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="pt-1 flex items-center gap-2">
                            <button type="button"
                                @click="openEditLocation({{ Js::from($loc) }}, '{{ route('hrm.locations.update', $loc->id) }}')"
                                class="flex-1 h-11 px-3 rounded-[12px] text-[13px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#0071E3] hover:text-white active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                <span>{{ __('hrm.edit_office_location') }}</span>
                            </button>

                            @if(! $loc->is_primary && $allLocations->count() > 1)
                                <form method="POST" action="{{ route('hrm.locations.destroy', $loc->id) }}"
                                    onsubmit="return confirm('{{ addslashes(__('hrm.confirm_delete_location')) }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="h-11 w-11 rounded-[12px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all flex items-center justify-center cursor-pointer shrink-0"
                                        title="{{ __('hrm.tooltip_delete_location') }}"
                                        aria-label="{{ __('hrm.tooltip_delete_location') }}">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-black/50 dark:text-white/50 font-medium">
                        <i data-lucide="map-pin-off" class="w-8 h-8 mx-auto mb-1.5 text-black/30 dark:text-white/30"></i>
                        <p class="text-xs">{{ __('hrm.empty_locations_list') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 4B: SHIFT KERJA KARYAWAN (WORK SHIFTS)               -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'shifts'" class="space-y-4" x-transition.opacity style="display: none;">
        <!-- Penjelasan Konsep Jam Operasional vs Shift Kerja (Bento Alert) -->
        <div class="p-4.5 rounded-[18px] bg-gradient-to-r from-[#30B0C7]/10 via-[#007AFF]/5 to-transparent border border-[#30B0C7]/20 flex items-start gap-3.5">
            <div class="w-9 h-9 rounded-[11px] bg-[#30B0C7]/15 text-[#30B0C7] flex items-center justify-center shrink-0 mt-0.5">
                <i data-lucide="info" class="w-5 h-5"></i>
            </div>
            <div class="text-xs sm:text-[13px] leading-relaxed text-black/80 dark:text-white/80">
                <strong class="font-bold text-black dark:text-white block mb-0.5">Pemisahan Jam Operasional Outlet & Shift Kerja:</strong>
                Operating hours outlet menentukan jam buka toko, sedangkan <strong>Shift Kerja</strong> menentukan jadwal wajib karyawan (Pagi, Siang, Malam/Overnight). Sistem menghitung keterlambatan dan kepulangan lebih awal secara otomatis berdasarkan shift aktif dan toleransi (grace period) yang dikonfigurasi.
            </div>
        </div>

        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <!-- Desktop Table View (>= 768px) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse min-w-[950px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            <th class="py-3.5 px-4 sm:px-5">{{ __('hrm.th_shift_name_code') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_shift_hours') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_break_net_hours') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_grace_period') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_branch_coverage') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_status') }}</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('hrm.th_action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($workShifts as $ws)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors group">
                                <td class="py-3.5 px-4 sm:px-5 font-bold text-black dark:text-white">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $ws->color ?: '#30B0C7' }}"></span>
                                        <div>
                                            <span class="block text-[13.5px] font-bold">{{ $ws->name }}</span>
                                            @if($ws->code)
                                                <span class="text-[11px] font-mono text-black/50 dark:text-white/50 uppercase tracking-wider font-semibold">{{ $ws->code }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($ws->description)
                                        <div class="text-[11px] text-black/50 dark:text-white/50 font-normal mt-0.5 max-w-xs truncate">{{ $ws->description }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 tabular-nums">
                                    <div class="font-bold text-black dark:text-white text-[13.5px]">
                                        {{ $ws->start_time }} - {{ $ws->end_time }}
                                    </div>
                                    @if($ws->isOvernight())
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-[#AF52DE]/15 text-[#AF52DE] border border-[#AF52DE]/30 mt-0.5">
                                            <i data-lucide="moon" class="w-3 h-3"></i>
                                            <span>{{ __('hrm.badge_overnight_shift') }}</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-black/80 dark:text-white/80 font-medium">
                                    <div>{{ round($ws->net_work_minutes / 60, 1) }}{{ __('hrm.unit_work_hours') }}</div>
                                    <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('hrm.break_prefix') }}{{ $ws->break_duration_minutes }} {{ __('hrm.unit_minutes') }}</div>
                                </td>
                                <td class="py-3.5 px-3 tabular-nums">
                                    @if($ws->grace_period_minutes > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                            {{ $ws->grace_period_minutes }}{{ __('hrm.unit_minutes_cap') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-500/10 text-slate-500">
                                            {{ __('hrm.opt_grace_0_strict') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-[12.5px] text-black/70 dark:text-white/70">
                                    {{ $ws->location?->name ?? __('hrm.all_locations_global') }}
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($ws->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                            {{ __('hrm.status_active') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50">
                                            {{ __('hrm.status_inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button"
                                            @click="openEditShift({{ Js::from($ws) }}, '{{ route('hrm.shifts.update', $ws->id) }}')"
                                            class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#007AFF] hover:bg-[#007AFF]/10 dark:hover:bg-[#007AFF]/20 transition-all cursor-pointer shrink-0"
                                            title="{{ __('hrm.btn_edit') }}"
                                            aria-label="{{ __('hrm.btn_edit') }}">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>
                                        <form method="POST" action="{{ route('hrm.shifts.destroy', $ws->id) }}"
                                            onsubmit="return confirm('{{ addslashes(__('hrm.confirm_delete_shift')) }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#FF3B30] hover:bg-[#FF3B30]/10 dark:hover:bg-[#FF3B30]/20 transition-all cursor-pointer shrink-0"
                                                title="{{ __('hrm.tooltip_delete_shift') ?? 'Hapus Shift' }}"
                                                aria-label="{{ __('hrm.tooltip_delete_shift') ?? 'Hapus Shift' }}">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-black/50 dark:text-white/50 font-medium">
                                    <i data-lucide="clock" class="w-8 h-8 mx-auto mb-2 text-black/30 dark:text-white/30"></i>
                                    <p class="text-xs">{{ __('hrm.empty_shift_desc') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Bento Card View (< 768px) -->
            <div class="md:hidden divide-y divide-black/5 dark:divide-white/5">
                @forelse($workShifts as $ws)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $ws->color ?: '#30B0C7' }}"></span>
                                <span class="font-bold text-black dark:text-white text-[14px] truncate">{{ $ws->name }}</span>
                                @if($ws->code)
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-mono bg-black/5 dark:bg-white/10 font-bold uppercase">{{ $ws->code }}</span>
                                @endif
                            </div>
                            @if($ws->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#34C759] shrink-0">{{ __('hrm.status_active') }}</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50 shrink-0">{{ __('hrm.status_inactive') }}</span>
                            @endif
                        </div>

                        <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-black/55 dark:text-white/55">{{ __('hrm.label_work_hours') }}</span>
                                <span class="font-bold font-mono text-[13px] text-black dark:text-white">{{ $ws->start_time }} - {{ $ws->end_time }}</span>
                            </div>
                            @if($ws->isOvernight())
                                <div class="text-[10.5px] font-bold text-[#AF52DE] flex items-center gap-1">
                                    <i data-lucide="moon" class="w-3 h-3"></i>
                                    <span>{{ __('hrm.badge_overnight_shift_long') }}</span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between pt-1 border-t border-black/5 dark:border-white/5">
                                <span class="text-black/55 dark:text-white/55">{{ __('hrm.label_grace_period') }}</span>
                                <span class="font-semibold">{{ $ws->grace_period_minutes > 0 ? $ws->grace_period_minutes . ' ' . __('hrm.unit_minutes') : __('hrm.opt_grace_0_strict_lower') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-black/55 dark:text-white/55">{{ __('hrm.label_location') }}</span>
                                <span class="font-semibold">{{ $ws->location?->name ?? __('hrm.label_all_locations') }}</span>
                            </div>
                        </div>

                        <div class="pt-1 flex items-center gap-2">
                            <button type="button"
                                @click="openEditShift({{ Js::from($ws) }}, '{{ route('hrm.shifts.update', $ws->id) }}')"
                                class="flex-1 h-10 px-3 rounded-[11px] text-[12.5px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#0071E3] hover:text-white transition active:scale-[0.98] flex items-center justify-center gap-1.5 cursor-pointer">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                <span>{{ __('hrm.btn_edit_shift') }}</span>
                            </button>
                            <form method="POST" action="{{ route('hrm.shifts.destroy', $ws->id) }}"
                                onsubmit="return confirm('{{ addslashes(__('hrm.confirm_delete_shift')) }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="h-10 w-10 rounded-[11px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition flex items-center justify-center cursor-pointer shrink-0">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-black/50 dark:text-white/50 text-xs">
                        {{ __('hrm.empty_shift_short') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 4C: JADWAL & ROSTER KARYAWAN (EMPLOYEE SCHEDULES)    -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'schedules'" class="space-y-4" x-transition.opacity style="display: none;">
        <!-- Filter Controls Bar -->
        <form method="GET" action="{{ route('hrm.index') }}" class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_2px_10px_rgba(0,0,0,0.02)] space-y-3.5">
            <input type="hidden" name="tab" value="schedules">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-[11px] uppercase font-bold text-black/60 dark:text-white/60 mb-1">{{ __('hrm.th_employee') }}</label>
                    <select name="sch_user_id" class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs font-semibold text-black dark:text-white focus:ring-1 focus:ring-[#5856D6]">
                        <option value="">{{ __('hrm.filter_all_employees') }}</option>
                        @foreach($memberships as $m)
                            @if($m->user)
                                <option value="{{ $m->user->id }}" {{ $schUserId === $m->user->id ? 'selected' : '' }}>{{ $m->user->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] uppercase font-bold text-black/60 dark:text-white/60 mb-1">{{ __('hrm.field_work_shift') }}</label>
                    <select name="sch_shift_id" class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs font-semibold text-black dark:text-white focus:ring-1 focus:ring-[#5856D6]">
                        <option value="">{{ __('hrm.filter_all_shifts') }}</option>
                        @foreach($workShifts as $ws)
                            <option value="{{ $ws->id }}" {{ $schShiftId === $ws->id ? 'selected' : '' }}>{{ $ws->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] uppercase font-bold text-black/60 dark:text-white/60 mb-1">{{ __('hrm.field_schedule_type') }}</label>
                    <select name="sch_type" class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs font-semibold text-black dark:text-white focus:ring-1 focus:ring-[#5856D6]">
                        <option value="">{{ __('hrm.filter_all_types') }}</option>
                        <option value="recurring" {{ $schType === 'recurring' ? 'selected' : '' }}>{{ __('hrm.opt_recurring_weekly') }}</option>
                        <option value="specific_date" {{ $schType === 'specific_date' ? 'selected' : '' }}>{{ __('hrm.opt_specific_date') }}</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 h-10 px-4 rounded-[10px] bg-[#5856D6] hover:bg-[#4745B8] text-white text-xs font-bold transition cursor-pointer">
                        {{ __('hrm.apply_filter') }}
                    </button>
                    @if($schUserId || $schShiftId || $schType)
                        <a href="{{ route('hrm.index', ['tab' => 'schedules']) }}" class="h-10 px-3 rounded-[10px] bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60 text-xs font-semibold flex items-center justify-center hover:text-black dark:hover:text-white">
                            {{ __('hrm.reset_filter') }}
                        </a>
                    @endif
                </div>
            </div>
        </form>

        @php
            $dayLabels = [
                'monday' => __('hrm.day_monday'),
                'tuesday' => __('hrm.day_tuesday'),
                'wednesday' => __('hrm.day_wednesday'),
                'thursday' => __('hrm.day_thursday'),
                'friday' => __('hrm.day_friday'),
                'saturday' => __('hrm.day_saturday'),
                'sunday' => __('hrm.day_sunday'),
            ];
        @endphp

        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <!-- Desktop Table View (>= 768px) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse min-w-[950px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            <th class="py-3.5 px-4 sm:px-5">{{ __('hrm.th_employee') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_schedule_pattern') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_shift_assignment') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_valid_period') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_branch_location') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_notes') }}</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('hrm.th_action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($employeeSchedules as $sch)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors group">
                                <td class="py-3.5 px-4 sm:px-5 font-bold text-black dark:text-white">
                                    {{ $sch->user?->name ?? __('hrm.default_employee_name') }}
                                    <div class="text-[11px] text-black/50 dark:text-white/50 font-normal">{{ $sch->user?->email }}</div>
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($sch->schedule_type === 'recurring')
                                        <div class="font-bold text-[#5856D6] flex items-center gap-1.5">
                                            <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('hrm.every_day_prefix', ['day' => $dayLabels[$sch->day_of_week] ?? ucfirst($sch->day_of_week)]) }}</span>
                                        </div>
                                        <span class="text-[10.5px] text-black/50 dark:text-white/50">{{ __('hrm.recurring_roster_sub') }}</span>
                                    @else
                                        <div class="font-bold text-[#007AFF] tabular-nums">
                                            {{ $sch->specific_date ? \Carbon\Carbon::parse($sch->specific_date)->translatedFormat('d M Y') : '-' }}
                                        </div>
                                        <span class="text-[10.5px] text-black/50 dark:text-white/50">{{ __('hrm.opt_specific_date') }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($sch->is_off_day)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30] border border-[#FF3B30]/30">
                                            {{ __('hrm.badge_off_day') }}
                                        </span>
                                    @elseif($sch->workShift)
                                        <div class="font-bold text-black dark:text-white flex items-center gap-1.5">
                                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $sch->workShift->color ?: '#5856D6' }}"></span>
                                            <span>{{ $sch->workShift->name }}</span>
                                        </div>
                                        <div class="text-[11px] text-black/60 dark:text-white/60 font-mono tabular-nums">
                                            {{ $sch->workShift->start_time }} - {{ $sch->workShift->end_time }}
                                            @if($sch->workShift->isOvernight()) (+1) @endif
                                        </div>
                                    @else
                                        <span class="text-black/40 dark:text-white/40">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-[11.5px] tabular-nums text-black/70 dark:text-white/70">
                                    @if($sch->effective_date || $sch->end_date)
                                        <div>{{ __('hrm.shift_starts_at') }} {{ $sch->effective_date ? \Carbon\Carbon::parse($sch->effective_date)->format('d/m/Y') : __('hrm.shift_always') }}</div>
                                        <div>{{ __('hrm.shift_ends_at') }} {{ $sch->end_date ? \Carbon\Carbon::parse($sch->end_date)->format('d/m/Y') : __('hrm.shift_indefinite') }}</div>
                                    @else
                                        <span class="text-black/40 dark:text-white/40">{{ __('hrm.shift_permanent') }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-[12.5px] text-black/70 dark:text-white/70">
                                    {{ $sch->location?->name ?? __('hrm.all_locations_global') }}
                                </td>
                                <td class="py-3.5 px-3 text-[11.5px] text-black/60 dark:text-white/60 max-w-xs truncate">
                                    {{ $sch->notes ?: '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button"
                                            @click="openEditSchedule({{ Js::from($sch) }}, '{{ route('hrm.schedules.update', $sch->id) }}')"
                                            class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#5856D6] hover:bg-[#5856D6]/10 dark:hover:bg-[#5856D6]/20 transition-all cursor-pointer shrink-0"
                                            title="{{ __('hrm.btn_edit') }}"
                                            aria-label="{{ __('hrm.btn_edit') }}">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>
                                        <form method="POST" action="{{ route('hrm.schedules.destroy', $sch->id) }}"
                                            onsubmit="return confirm('{{ __('hrm.confirm_delete_schedule') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#FF3B30] hover:bg-[#FF3B30]/10 dark:hover:bg-[#FF3B30]/20 transition-all cursor-pointer shrink-0"
                                                title="{{ __('hrm.tooltip_delete_schedule') ?? 'Hapus Jadwal' }}"
                                                aria-label="{{ __('hrm.tooltip_delete_schedule') ?? 'Hapus Jadwal' }}">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-black/50 dark:text-white/50 font-medium">
                                    <i data-lucide="calendar" class="w-8 h-8 mx-auto mb-2 text-black/30 dark:text-white/30"></i>
                                    <p class="text-xs">{{ __('hrm.empty_roster_desc') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Bento Card View (< 768px) -->
            <div class="md:hidden divide-y divide-black/5 dark:divide-white/5">
                @forelse($employeeSchedules as $sch)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-black dark:text-white text-[14px]">{{ $sch->user?->name ?? __('hrm.default_employee_name') }}</span>
                            @if($sch->is_off_day)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">{{ __('hrm.badge_off_day') }}</span>
                            @elseif($sch->workShift)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#5856D6]/15 text-[#5856D6]">{{ $sch->workShift->name }}</span>
                            @endif
                        </div>
                        <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-1.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-black/55 dark:text-white/55">{{ __('hrm.schedule_pattern') }}</span>
                                <span class="font-bold">
                                    @if($sch->schedule_type === 'recurring')
                                        {{ __('hrm.every_day_prefix', ['day' => $dayLabels[$sch->day_of_week] ?? ucfirst($sch->day_of_week)]) }}
                                    @else
                                        {{ $sch->specific_date ? \Carbon\Carbon::parse($sch->specific_date)->translatedFormat('d M Y') : '-' }}
                                    @endif
                                </span>
                            </div>
                            @if($sch->workShift && !$sch->is_off_day)
                                <div class="flex items-center justify-between">
                                    <span class="text-black/55 dark:text-white/55">{{ __('hrm.shift_hours') }}</span>
                                    <span class="font-mono font-bold">{{ $sch->workShift->start_time }} - {{ $sch->workShift->end_time }}</span>
                                </div>
                            @endif
                            @if($sch->notes)
                                <div class="text-[11px] text-black/60 dark:text-white/60 italic pt-1">
                                    "{{ $sch->notes }}"
                                </div>
                            @endif
                        </div>
                        <div class="pt-1 flex items-center gap-2">
                            <button type="button"
                                @click="openEditSchedule({{ Js::from($sch) }}, '{{ route('hrm.schedules.update', $sch->id) }}')"
                                class="flex-1 h-10 px-3 rounded-[11px] text-[12.5px] font-bold text-[#5856D6] bg-[#5856D6]/10 hover:bg-[#5856D6] hover:text-white transition active:scale-[0.98] flex items-center justify-center gap-1.5 cursor-pointer">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                <span>{{ __('hrm.btn_edit_schedule') }}</span>
                            </button>
                            <form method="POST" action="{{ route('hrm.schedules.destroy', $sch->id) }}"
                                onsubmit="return confirm('{{ __('hrm.confirm_delete_schedule') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="h-10 w-10 rounded-[11px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition flex items-center justify-center cursor-pointer shrink-0">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-black/50 dark:text-white/50 text-xs">
                        {{ __('hrm.empty_roster_desc') }}
                    </div>
                @endforelse
            </div>

            @if($employeeSchedules->hasPages())
                <div class="p-3.5 border-t border-black/5 dark:border-white/10">
                    {{ $employeeSchedules->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 5: PENGGAJIAN BULANAN (PAYROLL RUNS)                -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'payrolls'" class="space-y-4" x-transition.opacity style="display: none;">
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <!-- Desktop View (>= 768px) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse min-w-[950px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            <th class="py-3.5 px-4 sm:px-5">{{ __('hrm.col_payroll_period') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_employee_count') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_total_gross') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_total_deductions') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_take_home_pay') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_company_burden') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_status') }}</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('hrm.col_action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($payrolls as $p)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors group">
                                <td class="py-3.5 px-4 sm:px-5 font-bold text-black dark:text-white">
                                    <a href="{{ route('hrm.payrolls.show', $p->id) }}" class="hover:text-[#007AFF] transition">
                                        {{ $p->title }}
                                    </a>
                                    <div class="text-[11.5px] text-black/50 dark:text-white/50 font-normal">
                                        {{ __('hrm.created_at_prefix') }} {{ $p->created_at->translatedFormat('d M Y') }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-semibold text-black dark:text-white">
                                    {{ $p->total_employees_count }} {{ __('hrm.people_unit') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-black dark:text-white font-medium">
                                    Rp {{ number_format((float)$p->total_gross_pay, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-[#FF3B30] font-semibold">
                                    -Rp {{ number_format((float)$p->total_deductions, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-extrabold text-[#34C759]">
                                    Rp {{ number_format((float)$p->total_take_home_pay, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-bold text-black dark:text-white">
                                    Rp {{ number_format((float)$p->total_company_cost, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($p->status === 'paid')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/30">
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('hrm.status_paid_badge') }}</span>
                                        </span>
                                    @elseif($p->status === 'approved')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/15 text-[#007AFF] border border-[#007AFF]/30">
                                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('hrm.status_approved_badge') }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/30">
                                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('hrm.status_draft_badge') }}</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('hrm.payrolls.show', $p->id) }}"
                                            class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#007AFF] hover:bg-[#007AFF]/10 dark:hover:bg-[#007AFF]/20 transition-all cursor-pointer shrink-0"
                                            title="{{ __('hrm.detail_and_slip') }}"
                                            aria-label="{{ __('hrm.detail_and_slip') }}">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>

                                        @if($p->status === 'draft')
                                            <form method="POST" action="{{ route('hrm.payrolls.destroy', $p->id) }}"
                                                onsubmit="return confirm('{{ addslashes(__('hrm.confirm_delete_payroll')) }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#FF3B30] hover:bg-[#FF3B30]/10 dark:hover:bg-[#FF3B30]/20 transition-all cursor-pointer shrink-0"
                                                    title="{{ __('hrm.tooltip_delete_draft') }}"
                                                    aria-label="{{ __('hrm.tooltip_delete_draft') }}">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-black/50 dark:text-white/50 font-medium">
                                    {{ __('hrm.empty_payrolls_list') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Bento Card View (< 768px) -->
            <div class="md:hidden divide-y divide-black/5 dark:divide-white/5">
                @forelse($payrolls as $p)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <a href="{{ route('hrm.payrolls.show', $p->id) }}" class="font-bold text-[14px] text-black dark:text-white hover:text-[#007AFF] transition block truncate">
                                    {{ $p->title }}
                                </a>
                                <div class="text-[11px] text-black/50 dark:text-white/50">
                                    {{ __('hrm.created_at_prefix') }} {{ $p->created_at->translatedFormat('d M Y') }} · <strong class="text-black/70 dark:text-white/70">{{ $p->total_employees_count }} {{ __('hrm.people_unit') }}</strong>
                                </div>
                            </div>
                            @if($p->status === 'paid')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/30 shrink-0">
                                    <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                                    <span>{{ __('hrm.status_paid_badge') }}</span>
                                </span>
                            @elseif($p->status === 'approved')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#007AFF]/15 text-[#007AFF] border border-[#007AFF]/30 shrink-0">
                                    <i data-lucide="shield-check" class="w-3 h-3"></i>
                                    <span>{{ __('hrm.status_approved_badge') }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/30 shrink-0">
                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                    <span>{{ __('hrm.status_draft_badge') }}</span>
                                </span>
                            @endif
                        </div>

                        <!-- Financial Bento Grid -->
                        <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-2">
                            <div class="flex items-center justify-between text-[12px]">
                                <span class="text-black/60 dark:text-white/60">{{ __('hrm.col_total_gross') }}</span>
                                <span class="font-semibold text-black dark:text-white tabular-nums">
                                    Rp {{ number_format((float)$p->total_gross_pay, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-[12px]">
                                <span class="text-black/60 dark:text-white/60">{{ __('hrm.col_total_deductions') }}</span>
                                <span class="font-semibold text-[#FF3B30] tabular-nums">
                                    -Rp {{ number_format((float)$p->total_deductions, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="pt-2 border-t border-black/5 dark:border-white/10 flex items-center justify-between">
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 block">{{ __('hrm.col_take_home_pay') }}</span>
                                    <span class="text-[15px] font-extrabold text-[#34C759] tabular-nums">
                                        Rp {{ number_format((float)$p->total_take_home_pay, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-black/40 dark:text-white/40 block">{{ __('hrm.col_company_burden') }}</span>
                                    <span class="text-[12px] font-bold text-black/80 dark:text-white/80 tabular-nums">
                                        Rp {{ number_format((float)$p->total_company_cost, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="pt-1 flex items-center gap-2">
                            <a href="{{ route('hrm.payrolls.show', $p->id) }}"
                                class="flex-1 h-11 px-4 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-[0_2px_8px_rgba(0,122,255,0.25)]">
                                <span>{{ __('hrm.detail_and_slip') }}</span>
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>

                            @if($p->status === 'draft')
                                <form method="POST" action="{{ route('hrm.payrolls.destroy', $p->id) }}"
                                    onsubmit="return confirm('{{ addslashes(__('hrm.confirm_delete_payroll')) }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="h-11 w-11 rounded-[12px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all flex items-center justify-center cursor-pointer shrink-0"
                                        title="{{ __('hrm.tooltip_delete_draft') }}"
                                        aria-label="{{ __('hrm.tooltip_delete_draft') }}">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-black/50 dark:text-white/50 font-medium">
                        <i data-lucide="receipt-x" class="w-8 h-8 mx-auto mb-1.5 text-black/30 dark:text-white/30"></i>
                        <p class="text-xs">{{ __('hrm.empty_payrolls_list') }}</p>
                    </div>
                @endforelse
            </div>

            @if($payrolls->hasPages())
                <div class="p-3.5 border-t border-black/5 dark:border-white/10">
                    {{ $payrolls->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 6: KASBON & PINJAMAN KARYAWAN                        -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'loans'" class="space-y-4" x-transition.opacity style="display: none;">
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <!-- Desktop View (>= 768px) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse min-w-[950px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            <th class="py-3.5 px-4 sm:px-5">{{ __('hrm.col_loan_number') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_employee_name') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_target_date') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.principal_ceiling') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_tenor') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.installment_per_month') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.remaining_balance_label') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_status') }}</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('hrm.col_action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($loans as $loan)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors group">
                                <td class="py-3.5 px-4 sm:px-5 font-bold text-black dark:text-white tabular-nums">
                                    {{ $loan->loan_number }}
                                </td>
                                <td class="py-3.5 px-3 font-semibold text-black dark:text-white">
                                    {{ $loan->user?->name ?? __('hrm.default_employee_name') }}
                                </td>
                                <td class="py-3.5 px-3 text-black/70 dark:text-white/70 tabular-nums font-medium">
                                    {{ \Carbon\Carbon::parse($loan->loan_date)->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-bold text-black dark:text-white">
                                    Rp {{ number_format((float)$loan->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-black/80 dark:text-white/80 font-medium">
                                    {{ $loan->tenor_months }} {{ __('hrm.unit_months') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-[#FF9500] font-bold">
                                    Rp {{ number_format((float)$loan->monthly_installment, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-extrabold {{ $loan->remaining_balance > 0 ? 'text-[#FF3B30]' : 'text-[#34C759]' }}">
                                    Rp {{ number_format((float)$loan->remaining_balance, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($loan->status === 'completed' || $loan->remaining_balance <= 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                            {{ __('hrm.status_paid_off') }}
                                        </span>
                                    @elseif($loan->status === 'cancelled')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-black/10 dark:bg-white/10 text-black/60 dark:text-white/60">
                                            {{ __('hrm.status_cancelled') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500]">
                                            {{ __('hrm.status_active_loan') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end">
                                        @if($loan->status === 'active' && $loan->remaining_balance > 0)
                                            <form method="POST" action="{{ route('hrm.loans.cancel', $loan->id) }}"
                                                onsubmit="return confirm('{{ addslashes(__('hrm.confirm_cancel_loan')) }}')">
                                                @csrf
                                                <button type="submit"
                                                    class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#FF3B30] hover:bg-[#FF3B30]/10 dark:hover:bg-[#FF3B30]/20 transition-all cursor-pointer shrink-0"
                                                    title="{{ __('hrm.btn_cancel') }}"
                                                    aria-label="{{ __('hrm.btn_cancel') }}">
                                                    <i data-lucide="ban" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-[11px] text-black/40 dark:text-white/40">-</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-black/50 dark:text-white/50 font-medium">
                                    {{ __('hrm.empty_loans_list') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Bento Card View (< 768px) -->
            <div class="md:hidden divide-y divide-black/5 dark:divide-white/5">
                @forelse($loans as $loan)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <span class="font-bold text-black dark:text-white text-[13px] tabular-nums block truncate">
                                    {{ $loan->loan_number }}
                                </span>
                                <span class="text-[11px] text-black/50 dark:text-white/50">
                                    {{ \Carbon\Carbon::parse($loan->loan_date)->translatedFormat('d M Y') }}
                                </span>
                            </div>
                            @if($loan->status === 'completed' || $loan->remaining_balance <= 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#34C759] shrink-0">
                                    {{ __('hrm.status_paid_off') }}
                                </span>
                            @elseif($loan->status === 'cancelled')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold bg-black/10 dark:bg-white/10 text-black/60 dark:text-white/60 shrink-0">
                                    {{ __('hrm.status_cancelled') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#FF9500]/15 text-[#FF9500] shrink-0">
                                    {{ __('hrm.status_active_loan') }}
                                </span>
                            @endif
                        </div>

                        <div class="font-bold text-[14px] text-black dark:text-white">
                            {{ $loan->user?->name ?? __('hrm.default_employee_name') }}
                        </div>

                        <!-- Loan Metrics Grid -->
                        <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-2">
                            <div class="grid grid-cols-2 gap-2 text-[12px]">
                                <div>
                                    <span class="text-[10.5px] text-black/50 dark:text-white/50 block font-medium">{{ __('hrm.principal_ceiling') }}</span>
                                    <span class="font-bold text-black dark:text-white tabular-nums">
                                        Rp {{ number_format((float)$loan->amount, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10.5px] text-black/50 dark:text-white/50 block font-medium">{{ __('hrm.col_tenor') }}</span>
                                    <span class="font-semibold text-black/80 dark:text-white/80 tabular-nums">
                                        {{ $loan->tenor_months }} {{ __('hrm.unit_months') }}
                                    </span>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-black/5 dark:border-white/10 grid grid-cols-2 gap-2">
                                <div>
                                    <span class="text-[10.5px] text-black/50 dark:text-white/50 block font-medium">{{ __('hrm.installment_per_month') }}</span>
                                    <span class="font-bold text-[#FF9500] tabular-nums text-[13px]">
                                        Rp {{ number_format((float)$loan->monthly_installment, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10.5px] text-black/50 dark:text-white/50 block font-medium">{{ __('hrm.remaining_balance_label') }}</span>
                                    <span class="font-extrabold tabular-nums text-[13px] {{ $loan->remaining_balance > 0 ? 'text-[#FF3B30]' : 'text-[#34C759]' }}">
                                        Rp {{ number_format((float)$loan->remaining_balance, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($loan->status === 'active' && $loan->remaining_balance > 0)
                            <div class="pt-1">
                                <form method="POST" action="{{ route('hrm.loans.cancel', $loan->id) }}"
                                    onsubmit="return confirm('{{ addslashes(__('hrm.confirm_cancel_loan')) }}')">
                                    @csrf
                                    <button type="submit"
                                        class="w-full h-11 px-4 rounded-[12px] text-[13px] font-bold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                        <i data-lucide="ban" class="w-4 h-4"></i>
                                        <span>{{ __('hrm.cancel_remaining_loan') }}</span>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-10 text-center text-black/50 dark:text-white/50 font-medium">
                        <i data-lucide="credit-card" class="w-8 h-8 mx-auto mb-1.5 text-black/30 dark:text-white/30"></i>
                        <p class="text-xs">{{ __('hrm.empty_loans_list') }}</p>
                    </div>
                @endforelse
            </div>

            @if($loans->hasPages())
                <div class="p-3.5 border-t border-black/5 dark:border-white/10">
                    {{ $loans->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 1: DAFTARKAN BIOMETRIK WAJAH KARYAWAN              -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showFaceRegisterModal" 
            x-cloak
            @keydown.escape.window="stopFaceRegCamera(); showFaceRegisterModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showFaceRegisterModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="stopFaceRegCamera(); showFaceRegisterModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showFaceRegisterModal = false">
                <div x-show="showFaceRegisterModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_28px_56px_rgba(0,0,0,0.3)] overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                                <i data-lucide="scan-face" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.face_registration_title') }}</h3>
                                <p class="text-[12px] text-black/60 dark:text-white/60">
                                    {{ __('hrm.staff_prefix') }} <strong class="text-black dark:text-white" x-text="selectedFaceEmployee?.user?.name"></strong>
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="stopFaceRegCamera(); showFaceRegisterModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <form method="POST" :action="faceRegisterAction" enctype="multipart/form-data"
                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                class="p-5 space-y-4 overflow-y-auto">
                @csrf
                <input type="hidden" name="photo" :value="faceRegPhotoData">

                <!-- Mode Switcher -->
                <div class="flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08]">
                    <button type="button" @click="faceRegMode = 'camera'; startFaceRegCamera()"
                        :class="faceRegMode === 'camera' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-bold shadow-xs' : 'text-black/60 dark:text-white/60 font-semibold'"
                        class="flex-1 py-2 text-[12.5px] rounded-[9px] transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="camera" class="w-4 h-4"></i>
                        <span>{{ __('hrm.capture_from_webcam') }}</span>
                    </button>
                    <button type="button" @click="faceRegMode = 'upload'; stopFaceRegCamera()"
                        :class="faceRegMode === 'upload' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-bold shadow-xs' : 'text-black/60 dark:text-white/60 font-semibold'"
                        class="flex-1 py-2 text-[12.5px] rounded-[9px] transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        <span>{{ __('hrm.upload_photo_file') }}</span>
                    </button>
                </div>

                <!-- Webcam Capture View -->
                <template x-if="faceRegMode === 'camera'">
                    <div class="space-y-3">
                        <div class="relative w-full aspect-square max-w-[320px] mx-auto rounded-[20px] overflow-hidden bg-black flex items-center justify-center border-2 border-[#34C759]/40 shadow-inner">
                            <video id="faceRegVideo" autoplay playsinline muted class="w-full h-full object-cover transform -scale-x-100"></video>
                            
                            <!-- Oval Face Frame Guide -->
                            <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                                <div class="w-[72%] h-[82%] rounded-[50%] border-2 border-dashed border-white/80 shadow-[0_0_0_9999px_rgba(0,0,0,0.45)] flex items-center justify-center">
                                    <span class="text-[11px] font-bold text-white/90 bg-black/60 px-3 py-1 rounded-full uppercase tracking-wider">{{ __('hrm.fit_face_here') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center">
                            <button type="button" @click="captureFaceRegSnapshot()"
                                class="h-11 px-6 rounded-[12px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(52,199,89,0.3)] transition active:scale-[0.98] cursor-pointer">
                                <i data-lucide="aperture" class="w-4.5 h-4.5"></i>
                                <span>{{ __('hrm.take_face_photo') }}</span>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- File Upload View -->
                <template x-if="faceRegMode === 'upload'">
                    <div class="space-y-3">
                        <label class="block w-full p-6 border-2 border-dashed border-black/15 dark:border-white/15 rounded-[18px] text-center hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                            <i data-lucide="image" class="w-8 h-8 text-[#007AFF] mx-auto mb-2"></i>
                            <span class="text-[13px] font-bold text-black dark:text-white block">{{ __('hrm.choose_clear_photo') }}</span>
                            <span class="text-[11.5px] text-black/55 dark:text-white/55 block mt-0.5">{{ __('hrm.photo_format_hint') }}</span>
                            <input type="file" accept="image/*" @change="handleFaceRegUpload($event)" class="hidden">
                        </label>
                    </div>
                </template>

                <!-- Photo Preview Section -->
                <template x-if="faceRegPreview">
                    <div class="p-3.5 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 flex items-center gap-3">
                        <img :src="faceRegPreview" class="w-14 h-14 rounded-[12px] object-cover border border-[#34C759]/40 shadow-xs">
                        <div class="min-w-0 flex-1">
                            <span class="text-[12.5px] font-bold text-[#34C759] flex items-center gap-1">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                <span>{{ __('hrm.photo_ready_extract') }}</span>
                            </span>
                            <p class="text-[11px] text-black/60 dark:text-white/60">{{ __('hrm.photo_encrypt_hint') }}</p>
                        </div>
                    </div>
                </template>

                <div class="pt-3 flex justify-end gap-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="stopFaceRegCamera(); showFaceRegisterModal = false"
                        class="h-10 px-4.5 rounded-[11px] text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                        {{ __('hrm.btn_cancel') }}
                    </button>
                    <button type="submit" :disabled="!faceRegPhotoData || isSubmittingForm"
                        :class="faceRegPhotoData && !isSubmittingForm ? 'bg-[#34C759] hover:bg-[#2FB34F] text-white cursor-pointer shadow-[0_2px_8px_rgba(52,199,89,0.3)]' : 'bg-black/10 dark:bg-white/10 text-black/40 dark:text-white/40 cursor-not-allowed opacity-60'"
                        class="h-10 px-5 rounded-[11px] text-[13px] font-bold transition active:scale-[0.98] flex items-center justify-center gap-2">
                        <template x-if="!isSubmittingForm">
                            <span>{{ __('hrm.btn_save_biometric') }}</span>
                        </template>
                        <template x-if="isSubmittingForm">
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ __('hrm.saving') }}</span>
                            </div>
                        </template>
                    </button>
            </form>
                </div>
            </div>
        </div>
    </template>

    <!-- ======================================================== -->
    <!-- ======================================================== -->
    <!-- MODAL 2: 1-DIRECTION FRONTAL BIOMETRIC FACE ATTENDANCE   -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showFaceAttendanceModal" 
            x-cloak
            @keydown.escape.window="stopAttendanceCamera(); showFaceAttendanceModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showFaceAttendanceModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="stopAttendanceCamera(); showFaceAttendanceModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-end sm:items-center justify-center p-0 sm:p-4" @click.self="showFaceAttendanceModal = false">
                <div x-show="showFaceAttendanceModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    @click.stop
                    class="relative w-full sm:max-w-lg bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[26px] border-t sm:border border-black/10 dark:border-white/10 shadow-[0_32px_64px_rgba(0,0,0,0.4)] overflow-hidden flex flex-col max-h-[92dvh] sm:max-h-[90vh]">
                    
                    <!-- Mobile Sheet Drag Indicator -->
                    <div class="sm:hidden w-12 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto mt-2.5 mb-0.5 shrink-0"></div>

                    <!-- Header -->
                    <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                <i data-lucide="scan-face" class="w-4.5 h-4.5"></i>
                            </div>
                            <div>
                                <h4 class="text-[14px] font-bold text-black dark:text-white">{{ __('hrm.face_attendance_title') ?? 'Presensi Wajah 1-Arah' }}</h4>
                                <span class="text-[11px] font-semibold text-black/50 dark:text-white/50" x-text="attendanceActionType === 'in' ? '{{ __('portal.clock_in_title') }}' : '{{ __('portal.clock_out_title') }}'"></span>
                            </div>
                        </div>
                        <button type="button" @click="stopAttendanceCamera(); showFaceAttendanceModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <!-- Body Camera & 1-Direction Frontal Flow -->
            <div class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1">
                
                <!-- Guide Banner -->
                <div class="p-3 rounded-[12px] bg-[#007AFF]/8 border border-[#007AFF]/20 text-[11.5px] font-medium text-black/80 dark:text-white/80 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                    <span>{{ __('portal.face_frontal_guide') }}</span>
                </div>

                <!-- Video HUD Container -->
                <div class="relative w-full aspect-square max-w-[320px] mx-auto rounded-[20px] overflow-hidden bg-black flex items-center justify-center shadow-xl border-2 transition-colors duration-300"
                    :class="faceVerifiedSuccess ? 'border-[#34C759]' : (faceSteady ? 'border-[#007AFF]' : (faceDetected ? 'border-[#FF9500]' : 'border-[#FF3B30]/60'))">
                    
                    <video id="livenessVideo" autoplay playsinline muted class="w-full h-full object-cover transform -scale-x-100"></video>
                    
                    <!-- Oval Face Frame Guide Overlay -->
                    <div class="absolute inset-0 pointer-events-none flex flex-col items-center justify-between p-3.5">
                        <!-- Top status badge -->
                        <div class="px-3.5 py-1.5 rounded-full bg-black/75 backdrop-blur-md border border-white/20 text-white text-[11px] font-bold flex items-center gap-2 shadow-lg">
                            <span class="w-2 h-2 rounded-full"
                                :class="faceVerifiedSuccess ? 'bg-[#34C759]' : (faceSteady ? 'bg-[#007AFF] animate-pulse' : (faceDetected ? 'bg-[#FF9500]' : 'bg-[#FF3B30] animate-ping'))"></span>
                            <span x-text="liveDetectionStatus"></span>
                        </div>

                        <!-- Center Oval Target Ring -->
                        <div class="relative w-[72%] h-[82%] rounded-[50%] border-4 transition-all duration-300 flex items-center justify-center"
                            :class="faceVerifiedSuccess ? 'border-[#34C759] scale-105 bg-[#34C759]/20 shadow-[0_0_30px_rgba(52,199,89,0.5)]' : (faceSteady ? 'border-[#007AFF] scale-102 shadow-[0_0_24px_rgba(0,122,255,0.4)]' : (faceDetected ? 'border-[#FF9500] border-dashed shadow-[0_0_0_9999px_rgba(0,0,0,0.45)]' : 'border-[#FF3B30] border-dashed shadow-[0_0_0_9999px_rgba(0,0,0,0.6)]'))">
                            
                            <template x-if="faceVerifiedSuccess">
                                <div class="w-14 h-14 rounded-full bg-[#34C759] flex items-center justify-center mx-auto animate-pulse shadow-xl shadow-[#34C759]/50">
                                    <i data-lucide="check" class="w-8 h-8 text-white"></i>
                                </div>
                            </template>
                        </div>

                        <!-- Bottom Helper Indicator -->
                        <div class="w-full text-center py-2 px-3 rounded-[12px] bg-black/80 backdrop-blur-md border border-white/20 shadow-md">
                            <p class="text-[11.5px] font-bold text-white tracking-wide" x-text="liveGuideText"></p>
                        </div>
                    </div>
                </div>

                <!-- Stability Progress Bar -->
                <div class="space-y-1.5 max-w-[320px] mx-auto">
                    <div class="flex justify-between items-center text-[11px] font-bold text-black/70 dark:text-white/70">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="scan" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>{{ __('portal.face_stability_label') }}</span>
                        </span>
                        <span class="tabular-nums font-mono text-[#007AFF]" x-text="stabilityProgress + '%'"></span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-black/10 dark:bg-white/10 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-[#007AFF] to-[#34C759] transition-all duration-150" :style="'width: ' + stabilityProgress + '%'"></div>
                    </div>
                </div>

                <!-- Error Alert Display -->
                <template x-if="attendanceErrorMessage">
                    <div class="p-3 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[11.5px] font-semibold text-[#FF3B30] flex items-start gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                        <span x-text="attendanceErrorMessage"></span>
                    </div>
                </template>

                <!-- Processing Spinner -->
                <div x-show="isSubmittingAttendance" class="text-center py-2 space-y-1">
                    <div class="inline-flex items-center gap-2 text-[#007AFF] font-bold text-[13px]">
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                        <span>{{ __('portal.map_submitting') }}</span>
                    </div>
                    <p class="text-[10.5px] text-black/50 dark:text-white/50">{{ __('portal.face_privacy_notice') }}</p>
            </div>
                </div>
            </div>
        </div>
    </template>

    <!-- ======================================================== -->
    <!-- ======================================================== -->
    <!-- MODAL 3: TAMBAH LOKASI KANTOR & GEOFENCE                 -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showAddLocationModal" 
            x-cloak
            @keydown.escape.window="showAddLocationModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showAddLocationModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showAddLocationModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showAddLocationModal = false">
                <div x-show="showAddLocationModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/10 dark:border-white/10 shadow-[0_24px_48px_rgba(0,0,0,0.25)] overflow-hidden flex flex-col max-h-[88vh]">
                    <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#FF2D55]/10 text-[#FF2D55] flex items-center justify-center shrink-0">
                                <i data-lucide="map-pin" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.modal_add_location_title') }}</h3>
                                <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.modal_add_location_subtitle') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showAddLocationModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <form method="POST" action="{{ route('hrm.locations.store') }}"
                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                class="p-5 overflow-y-auto space-y-4">
                @csrf
                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.field_location_name') }} <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" required placeholder="{{ __('hrm.field_location_name_ph') }}"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF2D55]/50">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.field_city') }}</label>
                        <input type="text" name="city" placeholder="{{ __('hrm.field_city_ph') }}"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.field_geofence_radius') }} <span class="text-[#FF3B30]">*</span></label>
                        <input type="number" name="geofence_radius_meters" required min="10" max="5000" value="50"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white tabular-nums">
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.field_address') }}</label>
                    <textarea name="address" rows="2" placeholder="{{ __('hrm.field_address_ph') }}"
                        class="w-full p-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white"></textarea>
                </div>

                <!-- 1-Click GPS Coordinate Detector -->
                <div class="p-3.5 rounded-[14px] bg-[#007AFF]/[0.04] border border-[#007AFF]/15 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[12px] font-bold text-[#007AFF] flex items-center gap-1.5">
                            <i data-lucide="crosshair" class="w-4 h-4"></i>
                            <span>{{ __('hrm.gps_coordinates_label') }}</span>
                        </span>
                        <button type="button" @click="fillGpsToLocationForm('add')"
                            class="h-8 px-3 rounded-[9px] bg-[#007AFF] text-white text-[11.5px] font-bold flex items-center gap-1 hover:bg-[#0071E3] transition active:scale-[0.98] cursor-pointer">
                            <i data-lucide="navigation" class="w-3.5 h-3.5"></i>
                            <span>{{ __('hrm.btn_use_my_gps') }}</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-1">Latitude</label>
                            <input type="number" step="any" id="add_loc_lat" name="latitude" required placeholder="{{ __('hrm.placeholder_lat') }}"
                                class="w-full h-11 sm:h-10 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-mono font-bold text-black dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-1">Longitude</label>
                            <input type="number" step="any" id="add_loc_lng" name="longitude" required placeholder="{{ __('hrm.placeholder_lng') }}"
                                class="w-full h-11 sm:h-10 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-mono font-bold text-black dark:text-white">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-1">
                    <label class="flex items-center gap-2 text-[12.5px] font-bold text-black dark:text-white cursor-pointer">
                        <input type="checkbox" name="is_primary" value="1" class="w-4.5 h-4.5 rounded-[5px] text-[#FF2D55]">
                        <span>{{ __('hrm.opt_make_primary') }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-[12.5px] font-bold text-black dark:text-white cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4.5 h-4.5 rounded-[5px] text-[#34C759]">
                        <span>{{ __('hrm.opt_status_active') }}</span>
                    </label>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showAddLocationModal = false"
                        class="h-11 sm:h-10 px-4.5 rounded-[11px] text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                        {{ __('hrm.btn_cancel') }}
                    </button>
                    <button type="submit" :disabled="isSubmittingForm"
                        :class="isSubmittingForm ? 'opacity-60 cursor-not-allowed' : ''"
                        class="h-11 sm:h-10 px-5 rounded-[11px] text-[13px] font-bold text-white bg-[#FF2D55] hover:bg-[#E02648] transition active:scale-[0.98] shadow-[0_2px_8px_rgba(255,45,85,0.3)] cursor-pointer flex items-center justify-center gap-2">
                        <template x-if="!isSubmittingForm">
                            <span>{{ __('hrm.btn_save_location') }}</span>
                        </template>
                        <template x-if="isSubmittingForm">
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ __('hrm.saving') }}</span>
                            </div>
                        </template>
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>
    </template>

    <!-- ======================================================== -->
    <!-- MODAL 4: EDIT LOKASI KANTOR                              -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showEditLocationModal" 
            x-cloak
            @keydown.escape.window="showEditLocationModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showEditLocationModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showEditLocationModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showEditLocationModal = false">
                <div x-show="showEditLocationModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/10 dark:border-white/10 shadow-[0_24px_48px_rgba(0,0,0,0.25)] overflow-hidden flex flex-col max-h-[88vh]">
                    <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                <i data-lucide="edit-3" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.modal_edit_location_title') }}</h3>
                                <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.modal_edit_location_subtitle') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showEditLocationModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <form method="POST" :action="locationEditAction"
                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                class="p-5 overflow-y-auto space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.field_location_name') }} <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" required :value="selectedLocation?.name || ''"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-semibold text-black dark:text-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.field_city') }}</label>
                        <input type="text" name="city" :value="selectedLocation?.city || ''"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.field_geofence_radius') }} <span class="text-[#FF3B30]">*</span></label>
                        <input type="number" name="geofence_radius_meters" required min="10" max="5000" :value="selectedLocation?.geofence_radius_meters || 50"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white tabular-nums">
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.field_address') }}</label>
                    <textarea name="address" rows="2" :value="selectedLocation?.address || ''"
                        class="w-full p-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white"></textarea>
                </div>

                <!-- 1-Click GPS Coordinate Detector -->
                <div class="p-3.5 rounded-[14px] bg-[#007AFF]/[0.04] border border-[#007AFF]/15 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[12px] font-bold text-[#007AFF] flex items-center gap-1.5">
                            <i data-lucide="crosshair" class="w-4 h-4"></i>
                            <span>{{ __('hrm.gps_coordinates_label') }}</span>
                        </span>
                        <button type="button" @click="fillGpsToLocationForm('edit')"
                            class="h-8 px-3 rounded-[9px] bg-[#007AFF] text-white text-[11.5px] font-bold flex items-center gap-1 hover:bg-[#0071E3] transition active:scale-[0.98] cursor-pointer">
                            <i data-lucide="navigation" class="w-3.5 h-3.5"></i>
                            <span>{{ __('hrm.btn_use_my_gps') }}</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-1">Latitude</label>
                            <input type="number" step="any" name="latitude" required :value="selectedLocation?.latitude"
                                class="w-full h-11 sm:h-10 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-mono font-bold text-black dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-1">Longitude</label>
                            <input type="number" step="any" name="longitude" required :value="selectedLocation?.longitude"
                                class="w-full h-11 sm:h-10 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-mono font-bold text-black dark:text-white">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-1">
                    <label class="flex items-center gap-2 text-[12.5px] font-bold text-black dark:text-white cursor-pointer">
                        <input type="checkbox" name="is_primary" value="1" :checked="selectedLocation?.is_primary" class="w-4.5 h-4.5 rounded-[5px] text-[#FF2D55]">
                        <span>{{ __('hrm.opt_make_primary') }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-[12.5px] font-bold text-black dark:text-white cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" :checked="selectedLocation?.is_active" class="w-4.5 h-4.5 rounded-[5px] text-[#34C759]">
                        <span>{{ __('hrm.opt_status_active') }}</span>
                    </label>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showEditLocationModal = false"
                        class="h-11 sm:h-10 px-4.5 rounded-[11px] text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                        {{ __('hrm.btn_cancel') }}
                    </button>
                    <button type="submit" :disabled="isSubmittingForm"
                        :class="isSubmittingForm ? 'opacity-60 cursor-not-allowed' : ''"
                        class="h-11 sm:h-10 px-5 rounded-[11px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] transition active:scale-[0.98] shadow-[0_2px_8px_rgba(0,122,255,0.3)] cursor-pointer flex items-center justify-center gap-2">
                        <template x-if="!isSubmittingForm">
                            <span>{{ __('hrm.btn_update_location') }}</span>
                        </template>
                        <template x-if="isSubmittingForm">
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ __('hrm.saving') }}</span>
                            </div>
                        </template>
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>
    </template>

    <!-- ======================================================== -->
    <!-- ======================================================== -->
    <!-- MODAL 5: TAMBAH KARYAWAN BARU LENGKAP (XXL CANVAS 2-KOLOM)-->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showAddEmployeeModal" 
            x-cloak
            @keydown.escape.window="showAddEmployeeModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showAddEmployeeModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showAddEmployeeModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showAddEmployeeModal = false">
                <div x-show="showAddEmployeeModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-5xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_32px_64px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[90vh]">
                    <!-- Modal Header -->
                    <div class="px-6 py-4.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between bg-black/[0.01] dark:bg-white/[0.01]">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0 shadow-xs">
                                <i data-lucide="user-plus" class="w-5.5 h-5.5"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('hrm.modal_add_emp_title') }}</h3>
                                    <span class="px-2 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] text-[10.5px] font-bold uppercase tracking-wider">Canvas XXL</span>
                                </div>
                                <p class="text-[12.5px] text-black/60 dark:text-white/60">{{ __('hrm.modal_add_emp_subtitle') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showAddEmployeeModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <!-- Modal Form with 2-Column XXL Ergonomic Grid -->
            <form method="POST" action="{{ route('hrm.employees.store') }}" enctype="multipart/form-data"
                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                class="p-6 overflow-y-auto space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                    <!-- ==================== KOLOM KIRI ==================== -->
                    <div class="space-y-6">
                        <!-- Kluster 1: Identitas & Biodata Karyawan -->
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/8 dark:border-white/10 space-y-4">
                            <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/8">
                                <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                                    <div class="w-6 h-6 rounded-lg bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                        <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span>{{ __('hrm.cluster_1_title') }}</span>
                                </div>
                                <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">{{ __('hrm.cluster_1_sub') }}</span>
                            </div>

                            <div class="space-y-3.5">
                                <div>
                                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_fullname') }} <span class="text-[#FF3B30]">*</span></label>
                                    <input type="text" name="name" required placeholder="{{ __('hrm.field_fullname_ph') }}"
                                        class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_email_login') }} <span class="text-[#FF3B30]">*</span></label>
                                        <input type="email" name="email" required placeholder="{{ __('hrm.placeholder_email') }}"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition">
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_wa_number') }}</label>
                                        <input type="text" name="whatsapp_number" placeholder="{{ __('hrm.placeholder_wa') }}"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_nik_ktp') }}</label>
                                        <input type="text" name="nik_ktp" placeholder="{{ __('hrm.placeholder_nik') }}" maxlength="16"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-mono font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition">
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_npwp') }}</label>
                                        <input type="text" name="npwp" placeholder="{{ __('hrm.placeholder_npwp') }}"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-mono font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kluster 3: Skema Kompensasi & Rekening Penggajian -->
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/8 dark:border-white/10 space-y-4">
                            <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/8">
                                <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                                    <div class="w-6 h-6 rounded-lg bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                        <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span>{{ __('hrm.cluster_3_title') }}</span>
                                </div>
                                <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">{{ __('hrm.cluster_3_sub') }}</span>
                            </div>

                            <div class="space-y-3.5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_base_salary') }}</label>
                                        <input type="number" name="base_salary" step="1000" placeholder="{{ __('hrm.placeholder_salary') }}"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#34C759]/40 focus:border-[#34C759] transition">
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_daily_rate') }}</label>
                                        <input type="number" name="daily_rate" step="1000" placeholder="{{ __('hrm.placeholder_daily_rate') }}"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#34C759]/40 focus:border-[#34C759] transition">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_bank_name') }}</label>
                                        <input type="text" name="bank_name" placeholder="{{ __('hrm.placeholder_bank_name') }}"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#34C759]/40 focus:border-[#34C759] transition">
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_bank_account_num') }}</label>
                                        <input type="text" name="bank_account_number" placeholder="{{ __('hrm.field_bank_account_ph') }}"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#34C759]/40 focus:border-[#34C759] transition">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== KOLOM KANAN ==================== -->
                    <div class="space-y-6">
                        <!-- Kluster 2: Posisi, Struktur Organisasi & Penempatan -->
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/8 dark:border-white/10 space-y-4">
                            <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/8">
                                <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                                    <div class="w-6 h-6 rounded-lg bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                                        <i data-lucide="briefcase" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span>{{ __('hrm.cluster_2_title') }}</span>
                                </div>
                                <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">{{ __('hrm.cluster_2_sub') }}</span>
                            </div>

                            <div class="space-y-3.5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_access_role') }} <span class="text-[#FF3B30]">*</span></label>
                                        <select name="role_id" required class="w-full h-11 sm:h-10.5 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                            @foreach($availableRoles as $r)
                                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_specific_job') }}</label>
                                        <input type="text" name="job_title" placeholder="{{ __('hrm.field_specific_job_ph') }}"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_employment_type') }} <span class="text-[#FF3B30]">*</span></label>
                                        <select name="employment_type" required class="w-full h-11 sm:h-10.5 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                            <option value="permanent">{{ __('hrm.emp_type_permanent') }}</option>
                                            <option value="contract">{{ __('hrm.emp_type_contract') }}</option>
                                            <option value="daily_worker">{{ __('hrm.emp_type_daily') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_join_date') }}</label>
                                        <input type="date" name="join_date" value="{{ date('Y-m-d') }}"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_attendance_mode') }} <span class="text-[#FF3B30]">*</span></label>
                                        <select name="attendance_mode" required class="w-full h-11 sm:h-10.5 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                            <option value="geofenced">{{ __('hrm.att_mode_geofenced') }}</option>
                                            <option value="free">{{ __('hrm.att_mode_free') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_office_assignment') }}</label>
                                        <select name="primary_location_id" class="w-full h-11 sm:h-10.5 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                            <option value="">{{ __('hrm.opt_default_store_loc') }}</option>
                                            @foreach($locations as $loc)
                                                <option value="{{ $loc->id }}">{{ $loc->name }} (Radius: {{ $loc->geofence_radius_meters ?? 50 }}m)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">
                                        Shift Kerja Utama (Default)
                                    </label>
                                    <select name="default_shift_id" class="w-full h-11 sm:h-10.5 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                        <option value="">Bebas Jadwal / Ikuti Roster Mingguan</option>
                                        @foreach($workShifts as $ws)
                                            <option value="{{ $ws->id }}">
                                                {{ $ws->name }} ({{ $ws->start_time }} - {{ $ws->end_time }}{{ $ws->isOvernight() ? ' +1' : '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-[10.5px] text-black/50 dark:text-white/50 block mt-1">
                                        Shift acuan jika karyawan tidak memiliki jadwal spesifik pada tanggal absensi.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Kluster 4: Kepatuhan BPJS, Pajak PPh 21 TER & Biometrik -->
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/8 dark:border-white/10 space-y-4">
                            <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/8">
                                <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                                    <div class="w-6 h-6 rounded-lg bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center">
                                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span>{{ __('hrm.cluster_4_title') }}</span>
                                </div>
                                <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">{{ __('hrm.cluster_4_sub') }}</span>
                            </div>

                            <div class="space-y-3.5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_tax_ptkp') }}</label>
                                        <select name="tax_ptkp_status" class="w-full h-11 sm:h-10.5 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#AF52DE]/40 focus:border-[#AF52DE] transition">
                                            <option value="TK/0">TK/0 (Lajang 0 Tanggungan)</option>
                                            <option value="TK/1">TK/1 (Lajang 1 Tanggungan)</option>
                                            <option value="TK/2">TK/2 (Lajang 2 Tanggungan)</option>
                                            <option value="TK/3">TK/3 (Lajang 3 Tanggungan)</option>
                                            <option value="K/0">K/0 (Menikah 0 Tanggungan)</option>
                                            <option value="K/1">K/1 (Menikah 1 Tanggungan)</option>
                                            <option value="K/2">K/2 (Menikah 2 Tanggungan)</option>
                                            <option value="K/3">K/3 (Menikah 3 Tanggungan)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_dependents_count') }}</label>
                                        <input type="number" name="bpjs_dependents_count" min="0" max="10" value="0"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#AF52DE]/40 focus:border-[#AF52DE] transition">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                    <div class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/8 dark:border-white/8 space-y-2">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" id="add_bpjs_tk" name="bpjs_tk_enabled" value="1" checked class="w-4 h-4 rounded text-[#007AFF]">
                                            <label for="add_bpjs_tk" class="text-[12px] font-bold text-black dark:text-white cursor-pointer">{{ __('hrm.bpjs_tk_label') }}</label>
                                        </div>
                                        <input type="text" name="bpjs_tk_number" placeholder="{{ __('hrm.bpjs_tk_ph') }}"
                                            class="w-full h-10 sm:h-8.5 px-3 sm:px-2.5 rounded-[8px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[16px] sm:text-[11.5px] font-mono text-black dark:text-white">
                                    </div>
                                    <div class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/8 dark:border-white/8 space-y-2">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" id="add_bpjs_kes" name="bpjs_kes_enabled" value="1" checked class="w-4 h-4 rounded text-[#34C759]">
                                            <label for="add_bpjs_kes" class="text-[12px] font-bold text-black dark:text-white cursor-pointer">{{ __('hrm.bpjs_kes_label') }}</label>
                                        </div>
                                        <input type="text" name="bpjs_kes_number" placeholder="{{ __('hrm.bpjs_kes_ph') }}"
                                            class="w-full h-10 sm:h-8.5 px-3 sm:px-2.5 rounded-[8px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[16px] sm:text-[11.5px] font-mono text-black dark:text-white">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.upload_biometric_initial') }}</label>
                                    <input type="file" name="photo" accept="image/*"
                                        class="w-full text-[16px] sm:text-[12px] text-black/70 dark:text-white/70 file:mr-3 file:py-2.5 file:px-3.5 file:rounded-[10px] file:border-0 file:text-[13px] sm:file:text-[11.5px] file:font-bold file:bg-[#007AFF]/10 file:text-[#007AFF] hover:file:bg-[#007AFF]/20 transition cursor-pointer">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer Actions -->
                <div class="pt-4 flex items-center justify-between border-t border-black/5 dark:border-white/10">
                    <span class="text-[12px] text-black/40 dark:text-white/40 font-medium">{{ __('hrm.modal_add_emp_footer_note') }}</span>
                    <div class="flex items-center gap-2.5">
                        <button type="button" @click="showAddEmployeeModal = false"
                            class="h-11 sm:h-10.5 px-5 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                            {{ __('hrm.btn_cancel') }}
                        </button>
                        <button type="submit" :disabled="isSubmittingForm"
                            :class="isSubmittingForm ? 'opacity-60 cursor-not-allowed' : ''"
                            class="h-11 sm:h-10.5 px-6 rounded-[12px] text-[13.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] transition active:scale-[0.98] shadow-[0_2px_12px_rgba(0,122,255,0.3)] cursor-pointer flex items-center justify-center gap-2">
                            <template x-if="!isSubmittingForm">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span>{{ __('hrm.btn_save_employee') }}</span>
                                </div>
                            </template>
                            <template x-if="isSubmittingForm">
                                <div class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>{{ __('hrm.saving') }}</span>
                                </div>
                            </template>
                        </button>
                    </div>
            </form>
                </div>
            </div>
        </div>
    </template>

    <!-- ======================================================== -->
    <!-- MODAL 6: EDIT PROFIL HRM KARYAWAN (XXL CANVAS 2-KOLOM)   -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showEditEmployeeModal" 
            x-cloak
            @keydown.escape.window="showEditEmployeeModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showEditEmployeeModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showEditEmployeeModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showEditEmployeeModal = false">
                <div x-show="showEditEmployeeModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-5xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_32px_64px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[90vh]">
                    <!-- Modal Header -->
                    <div class="px-6 py-4.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between bg-black/[0.01] dark:bg-white/[0.01]">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0 shadow-xs">
                                <i data-lucide="edit-3" class="w-5.5 h-5.5"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('hrm.modal_edit_emp_title') }}</h3>
                                    <span class="px-2 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] text-[10.5px] font-bold uppercase tracking-wider">Canvas XXL</span>
                                </div>
                                <p class="text-[12.5px] text-black/60 dark:text-white/60">{{ __('hrm.modal_edit_emp_subtitle') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showEditEmployeeModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <!-- Modal Form with 2-Column XXL Ergonomic Grid -->
            <form method="POST" :action="editFormAction" enctype="multipart/form-data"
                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                class="p-6 overflow-y-auto space-y-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                    <!-- ==================== KOLOM KIRI ==================== -->
                    <div class="space-y-6">
                        <!-- Kluster 1: Identitas & Biodata Karyawan -->
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/8 dark:border-white/10 space-y-4">
                            <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/8">
                                <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                                    <div class="w-6 h-6 rounded-lg bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                        <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span>{{ __('hrm.cluster_1_title') }}</span>
                                </div>
                                <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">{{ __('hrm.cluster_1_sub') }}</span>
                            </div>

                            <div class="space-y-3.5">
                                <div>
                                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_fullname') }} <span class="text-[#FF3B30]">*</span></label>
                                    <input type="text" name="name" required :value="selectedEmployee?.user?.name || ''"
                                        class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_wa_number') }}</label>
                                        <input type="text" name="whatsapp_number" :value="selectedEmployee?.whatsapp_number || ''" placeholder="{{ __('hrm.placeholder_wa') }}"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition">
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_npwp') }}</label>
                                        <input type="text" name="npwp" :value="selectedEmployee?.npwp || ''" placeholder="{{ __('hrm.placeholder_npwp') }}"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-mono font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_nik_ktp') }}</label>
                                    <input type="text" name="nik_ktp" :value="selectedEmployee?.nik_ktp || ''" placeholder="{{ __('hrm.placeholder_nik') }}" maxlength="16"
                                        class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-mono font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition">
                                </div>
                            </div>
                        </div>

                        <!-- Kluster 3: Skema Kompensasi & Rekening Penggajian -->
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/8 dark:border-white/10 space-y-4">
                            <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/8">
                                <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                                    <div class="w-6 h-6 rounded-lg bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                        <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span>{{ __('hrm.cluster_3_title') }}</span>
                                </div>
                                <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">{{ __('hrm.cluster_3_sub') }}</span>
                            </div>

                            <div class="space-y-3.5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_base_salary') }}</label>
                                        <input type="number" name="base_salary" step="1000" :value="selectedEmployee?.base_salary || 0"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#34C759]/40 focus:border-[#34C759] transition">
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_daily_rate') }}</label>
                                        <input type="number" name="daily_rate" step="1000" :value="selectedEmployee?.daily_rate || 0"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#34C759]/40 focus:border-[#34C759] transition">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_bank_name') }}</label>
                                        <input type="text" name="bank_name" :value="selectedEmployee?.bank_name || ''" placeholder="{{ __('hrm.placeholder_bank_name') }}"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#34C759]/40 focus:border-[#34C759] transition">
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_bank_account_num') }}</label>
                                        <input type="text" name="bank_account_number" :value="selectedEmployee?.bank_account_number || ''" placeholder="{{ __('hrm.field_bank_account_ph') }}"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#34C759]/40 focus:border-[#34C759] transition">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== KOLOM KANAN ==================== -->
                    <div class="space-y-6">
                        <!-- Kluster 2: Posisi, Struktur Organisasi & Penempatan -->
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/8 dark:border-white/10 space-y-4">
                            <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/8">
                                <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                                    <div class="w-6 h-6 rounded-lg bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                                        <i data-lucide="briefcase" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span>{{ __('hrm.cluster_2_title') }}</span>
                                </div>
                                <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">{{ __('hrm.cluster_2_sub') }}</span>
                            </div>

                            <div class="space-y-3.5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_access_role') }} <span class="text-[#FF3B30]">*</span></label>
                                        <select name="role_id" required class="w-full h-11 sm:h-10.5 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                            @foreach($availableRoles as $r)
                                                <option value="{{ $r->id }}" :selected="selectedEmployee?.role_id === '{{ $r->id }}'">{{ $r->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_specific_job') }}</label>
                                        <input type="text" name="job_title" :value="selectedEmployee?.job_title || ''" placeholder="{{ __('hrm.field_specific_job_ph') }}"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_employment_type') }} <span class="text-[#FF3B30]">*</span></label>
                                        <select name="employment_type" required class="w-full h-11 sm:h-10.5 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                            <option value="permanent" :selected="selectedEmployee?.employment_type === 'permanent'">{{ __('hrm.emp_type_permanent') }}</option>
                                            <option value="contract" :selected="selectedEmployee?.employment_type === 'contract'">{{ __('hrm.emp_type_contract') }}</option>
                                            <option value="daily_worker" :selected="selectedEmployee?.employment_type === 'daily_worker'">{{ __('hrm.emp_type_daily') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_join_date') }}</label>
                                        <input type="date" name="join_date" :value="selectedEmployee?.join_date ? selectedEmployee.join_date.substring(0, 10) : ''"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_attendance_mode') }} <span class="text-[#FF3B30]">*</span></label>
                                        <select name="attendance_mode" required class="w-full h-11 sm:h-10.5 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                            <option value="geofenced" :selected="selectedEmployee?.attendance_mode === 'geofenced'">{{ __('hrm.att_mode_geofenced') }}</option>
                                            <option value="free" :selected="selectedEmployee?.attendance_mode === 'free'">{{ __('hrm.att_mode_free') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_office_assignment') }}</label>
                                        <select name="primary_location_id" class="w-full h-11 sm:h-10.5 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                            <option value="">{{ __('hrm.opt_default_store_loc') }}</option>
                                            @foreach($locations as $loc)
                                                <option value="{{ $loc->id }}" :selected="selectedEmployee?.primary_location_id === '{{ $loc->id }}'">{{ $loc->name }} (Radius: {{ $loc->geofence_radius_meters ?? 50 }}m)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">
                                        Shift Kerja Utama (Default)
                                    </label>
                                    <select name="default_shift_id" class="w-full h-11 sm:h-10.5 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                        <option value="">Bebas Jadwal / Ikuti Roster Mingguan</option>
                                        @foreach($workShifts as $ws)
                                            <option value="{{ $ws->id }}" :selected="selectedEmployee?.default_shift_id === '{{ $ws->id }}'">
                                                {{ $ws->name }} ({{ $ws->start_time }} - {{ $ws->end_time }}{{ $ws->isOvernight() ? ' +1' : '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-[10.5px] text-black/50 dark:text-white/50 block mt-1">
                                        Shift acuan jika karyawan tidak memiliki jadwal spesifik pada tanggal absensi.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Kluster 4: Kepatuhan BPJS, Pajak PPh 21 TER & Biometrik -->
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/8 dark:border-white/10 space-y-4">
                            <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/8">
                                <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                                    <div class="w-6 h-6 rounded-lg bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center">
                                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span>{{ __('hrm.cluster_4_title') }}</span>
                                </div>
                                <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">{{ __('hrm.cluster_4_sub') }}</span>
                            </div>

                            <div class="space-y-3.5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_tax_ptkp') }}</label>
                                        <select name="tax_ptkp_status" class="w-full h-11 sm:h-10.5 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#AF52DE]/40 focus:border-[#AF52DE] transition">
                                            <option value="TK/0" :selected="selectedEmployee?.tax_ptkp_status === 'TK/0'">TK/0 (Lajang 0 Tanggungan)</option>
                                            <option value="TK/1" :selected="selectedEmployee?.tax_ptkp_status === 'TK/1'">TK/1 (Lajang 1 Tanggungan)</option>
                                            <option value="TK/2" :selected="selectedEmployee?.tax_ptkp_status === 'TK/2'">TK/2 (Lajang 2 Tanggungan)</option>
                                            <option value="TK/3" :selected="selectedEmployee?.tax_ptkp_status === 'TK/3'">TK/3 (Lajang 3 Tanggungan)</option>
                                            <option value="K/0" :selected="selectedEmployee?.tax_ptkp_status === 'K/0'">K/0 (Menikah 0 Tanggungan)</option>
                                            <option value="K/1" :selected="selectedEmployee?.tax_ptkp_status === 'K/1'">K/1 (Menikah 1 Tanggungan)</option>
                                            <option value="K/2" :selected="selectedEmployee?.tax_ptkp_status === 'K/2'">K/2 (Menikah 2 Tanggungan)</option>
                                            <option value="K/3" :selected="selectedEmployee?.tax_ptkp_status === 'K/3'">K/3 (Menikah 3 Tanggungan)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_dependents_count') }}</label>
                                        <input type="number" name="bpjs_dependents_count" min="0" max="10" :value="selectedEmployee?.bpjs_dependents_count || 0"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#AF52DE]/40 focus:border-[#AF52DE] transition">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                    <div class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/8 dark:border-white/8 space-y-2">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" id="edit_bpjs_tk" name="bpjs_tk_enabled" value="1" :checked="selectedEmployee?.bpjs_tk_enabled" class="w-4 h-4 rounded text-[#007AFF]">
                                            <label for="edit_bpjs_tk" class="text-[12px] font-bold text-black dark:text-white cursor-pointer">{{ __('hrm.bpjs_tk_label') }}</label>
                                        </div>
                                        <input type="text" name="bpjs_tk_number" :value="selectedEmployee?.bpjs_tk_number || ''" placeholder="{{ __('hrm.bpjs_tk_ph') }}"
                                            class="w-full h-10 sm:h-8.5 px-3 sm:px-2.5 rounded-[8px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[16px] sm:text-[11.5px] font-mono text-black dark:text-white">
                                    </div>
                                    <div class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/8 dark:border-white/8 space-y-2">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" id="edit_bpjs_kes" name="bpjs_kes_enabled" value="1" :checked="selectedEmployee?.bpjs_kes_enabled" class="w-4 h-4 rounded text-[#34C759]">
                                            <label for="edit_bpjs_kes" class="text-[12px] font-bold text-black dark:text-white cursor-pointer">{{ __('hrm.bpjs_kes_label') }}</label>
                                        </div>
                                        <input type="text" name="bpjs_kes_number" :value="selectedEmployee?.bpjs_kes_number || ''" placeholder="{{ __('hrm.bpjs_kes_ph') }}"
                                            class="w-full h-10 sm:h-8.5 px-3 sm:px-2.5 rounded-[8px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[16px] sm:text-[11.5px] font-mono text-black dark:text-white">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.update_biometric_initial') }}</label>
                                    <input type="file" name="photo" accept="image/*"
                                        class="w-full text-[16px] sm:text-[12px] text-black/70 dark:text-white/70 file:mr-3 file:py-2.5 file:px-3.5 file:rounded-[10px] file:border-0 file:text-[13px] sm:file:text-[11.5px] file:font-bold file:bg-[#007AFF]/10 file:text-[#007AFF] hover:file:bg-[#007AFF]/20 transition cursor-pointer">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer Actions -->
                <div class="pt-4 flex items-center justify-between border-t border-black/5 dark:border-white/10">
                    <span class="text-[12px] text-black/40 dark:text-white/40 font-medium">{{ __('hrm.modal_edit_emp_footer_note') }}</span>
                    <div class="flex items-center gap-2.5">
                        <button type="button" @click="showEditEmployeeModal = false"
                            class="h-11 sm:h-10.5 px-5 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                            {{ __('hrm.btn_cancel') }}
                        </button>
                        <button type="submit" :disabled="isSubmittingForm"
                            :class="isSubmittingForm ? 'opacity-60 cursor-not-allowed' : ''"
                            class="h-11 sm:h-10.5 px-6 rounded-[12px] text-[13.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] transition active:scale-[0.98] shadow-[0_2px_12px_rgba(0,122,255,0.3)] cursor-pointer flex items-center justify-center gap-2">
                            <template x-if="!isSubmittingForm">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span>{{ __('hrm.btn_update_employee') }}</span>
                                </div>
                            </template>
                            <template x-if="isSubmittingForm">
                                <div class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>{{ __('hrm.saving') }}</span>
                                </div>
                            </template>
                        </button>
                    </div>
            </form>
                </div>
            </div>
        </div>
    </template>

    <!-- ======================================================== -->
    <!-- MODAL 7: CATAT KASBON BARU                               -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showAddLoanModal" 
            x-cloak
            @keydown.escape.window="showAddLoanModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showAddLoanModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showAddLoanModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showAddLoanModal = false">
                <div x-show="showAddLoanModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/10 dark:border-white/10 shadow-[0_24px_48px_rgba(0,0,0,0.25)] overflow-hidden flex flex-col max-h-[88vh]">
                    <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                                <i data-lucide="credit-card" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.modal_add_loan_title') }}</h3>
                                <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.modal_add_loan_subtitle') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showAddLoanModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <form method="POST" action="{{ route('hrm.loans.store') }}"
                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                class="p-5 space-y-4 overflow-y-auto">
                @csrf
                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.select_employee_label') }} <span class="text-[#FF3B30]">*</span></label>
                    <select name="user_id" required class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                        <option value="">{{ __('hrm.select_employee_ph') }}</option>
                        @foreach($memberships as $m)
                            <option value="{{ $m->user_id }}">{{ $m->user?->name ?? 'User' }} ({{ $m->job_title ?: ucfirst($m->role) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.loan_amount_label') }} <span class="text-[#FF3B30]">*</span></label>
                    <input type="number" name="amount" required step="10000" min="10000" placeholder="{{ __('hrm.loan_amount_ph') }}"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.tenor_months_label') }} <span class="text-[#FF3B30]">*</span></label>
                        <input type="number" name="tenor_months" required min="1" max="60" value="1"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-semibold text-black dark:text-white tabular-nums">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.loan_date_label') }}</label>
                        <input type="date" name="loan_date" required value="{{ date('Y-m-d') }}"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-semibold text-black dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.loan_purpose_label') }}</label>
                    <input type="text" name="purpose" placeholder="{{ __('hrm.loan_purpose_ph') }}"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white">
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showAddLoanModal = false"
                        class="h-11 sm:h-10 px-4.5 rounded-[11px] text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                        {{ __('hrm.btn_cancel') }}
                    </button>
                    <button type="submit" :disabled="isSubmittingForm"
                        :class="isSubmittingForm ? 'opacity-60 cursor-not-allowed' : ''"
                        class="h-11 sm:h-10 px-5 rounded-[11px] text-[13px] font-bold text-white bg-[#FF9500] hover:bg-[#E08500] transition active:scale-[0.98] shadow-[0_2px_8px_rgba(255,149,0,0.3)] cursor-pointer flex items-center justify-center gap-2">
                        <template x-if="!isSubmittingForm">
                            <span>{{ __('hrm.btn_save_loan') }}</span>
                        </template>
                        <template x-if="isSubmittingForm">
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ __('hrm.saving') }}</span>
                            </div>
                        </template>
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>
    </template>

    <!-- ======================================================== -->
    <!-- MODAL 8: AJUKAN TIKET PERBAIKAN ABSENSI                  -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showAddCorrectionModal" 
            x-cloak
            @keydown.escape.window="showAddCorrectionModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showAddCorrectionModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showAddCorrectionModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showAddCorrectionModal = false">
                <div x-show="showAddCorrectionModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-xl bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/10 dark:border-white/10 shadow-[0_24px_48px_rgba(0,0,0,0.25)] overflow-hidden flex flex-col max-h-[88vh]">
                    <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                                <i data-lucide="file-check-2" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.modal_add_correction_title') }}</h3>
                                <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.modal_add_correction_subtitle') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showAddCorrectionModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <form method="POST" action="{{ route('hrm.attendance.corrections.store') }}" enctype="multipart/form-data"
                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                class="p-5 overflow-y-auto space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.field_target_date') }} <span class="text-[#FF3B30]">*</span></label>
                        <input type="date" name="target_date" required max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-semibold text-black dark:text-white">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.field_correction_type') }} <span class="text-[#FF3B30]">*</span></label>
                        <select name="correction_type" required class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white">
                            <option value="clock_in_only">{{ __('hrm.corr_type_clock_in') }}</option>
                            <option value="clock_out_only">{{ __('hrm.corr_type_clock_out') }}</option>
                            <option value="full_day">{{ __('hrm.corr_type_full_day') }}</option>
                            <option value="status_only">{{ __('hrm.corr_type_status_only') }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.proposed_clock_in_label') }}</label>
                        <input type="time" name="proposed_clock_in" value="08:30"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.proposed_clock_out_label') }}</label>
                        <input type="time" name="proposed_clock_out" value="17:00"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.proposed_status_label') }}</label>
                        <select name="proposed_status" class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white">
                            <option value="present">{{ __('portal.status_present') }}</option>
                            <option value="late">{{ __('portal.status_late') }}</option>
                            <option value="half_day">{{ __('portal.status_half_day') }}</option>
                            <option value="sick">{{ __('portal.status_sick') }}</option>
                            <option value="leave">{{ __('portal.status_leave') }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.corr_reason_label') }} <span class="text-[#FF3B30]">*</span></label>
                    <textarea name="reason" required minlength="5" maxlength="1000" rows="3"
                        placeholder="{{ __('hrm.corr_reason_ph') }}"
                        class="w-full p-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white"></textarea>
                </div>

                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.attachment_label') }}</label>
                    <input type="file" name="attachment" accept="image/png,image/jpeg,image/webp,application/pdf"
                        class="w-full text-[16px] sm:text-[12.5px] text-black/70 dark:text-white/70 file:mr-3 file:py-2.5 sm:file:py-2 file:px-4 file:rounded-[10px] file:border-0 file:text-[13px] sm:file:text-[12px] file:font-bold file:bg-[#007AFF]/10 file:text-[#007AFF]">
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showAddCorrectionModal = false"
                        class="h-11 sm:h-10 px-4.5 rounded-[11px] text-[14px] sm:text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                        {{ __('hrm.btn_cancel') }}
                    </button>
                    <button type="submit" :disabled="isSubmittingForm"
                        :class="isSubmittingForm ? 'opacity-60 cursor-not-allowed' : ''"
                        class="h-11 sm:h-10 px-5 rounded-[11px] text-[14px] sm:text-[13px] font-bold text-white bg-[#FF9500] hover:bg-[#E08500] transition active:scale-[0.98] shadow-[0_2px_8px_rgba(255,149,0,0.3)] cursor-pointer flex items-center justify-center gap-2">
                        <template x-if="!isSubmittingForm">
                            <span>{{ __('hrm.btn_submit_correction') }}</span>
                        </template>
                        <template x-if="isSubmittingForm">
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ __('hrm.submitting') }}</span>
                            </div>
                        </template>
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>
    </template>

    <!-- ======================================================== -->
    <!-- MODAL 9: REVIEW PERSUTUJUAN TIKET KOREKSI (VISUAL DIFF)  -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showReviewCorrectionModal" 
            x-cloak
            @keydown.escape.window="showReviewCorrectionModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showReviewCorrectionModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showReviewCorrectionModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showReviewCorrectionModal = false">
                <div x-show="showReviewCorrectionModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/10 dark:border-white/10 shadow-[0_28px_56px_rgba(0,0,0,0.25)] overflow-hidden flex flex-col max-h-[88vh]">
                    <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.modal_review_correction_title') }}</span>
                                <span class="font-mono text-[14px] font-bold text-[#007AFF] px-2.5 py-0.5 rounded-[7px] bg-[#007AFF]/10" x-text="selectedCorrection?.correction_number"></span>
                                <template x-if="selectedCorrection?.status === 'pending'">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500]">{{ __('hrm.badge_pending_review') }}</span>
                                </template>
                                <template x-if="selectedCorrection?.status === 'approved'">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">{{ __('hrm.badge_approved') }}</span>
                                </template>
                                <template x-if="selectedCorrection?.status === 'rejected'">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">{{ __('hrm.badge_rejected') }}</span>
                                </template>
                            </div>
                            <p class="text-[12px] text-black/60 dark:text-white/60 font-medium">
                                {{ __('hrm.submitter_prefix') }} <strong class="text-black dark:text-white" x-text="selectedCorrection?.user?.name || '{{ __('hrm.default_employee_name') }}'"></strong>
                                &bull; {{ __('hrm.target_prefix') }} <span class="tabular-nums font-semibold" x-text="selectedCorrection?.target_date ? selectedCorrection.target_date.substring(0, 10) : '-'"></span>
                            </p>
                        </div>
                        <button type="button" @click="showReviewCorrectionModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <div class="p-5 overflow-y-auto space-y-5 text-[13px]">
                <!-- Visual Diff Comparison -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-2.5">
                        <div class="flex items-center gap-2 text-black/70 dark:text-white/70 font-bold text-[12.5px]">
                            <i data-lucide="database" class="w-4 h-4"></i>
                            <span>{{ __('hrm.sensor_original_record') }}</span>
                        </div>
                        <div class="space-y-1.5 text-[12px]">
                            <div class="flex justify-between">
                                <span class="text-black/55 dark:text-white/55">{{ __('hrm.clock_in_label') }}</span>
                                <span class="font-bold tabular-nums text-black dark:text-white" x-text="selectedCorrection?.attendance?.clock_in_at ? selectedCorrection.attendance.clock_in_at.substring(11, 16) + ' WIB' : '{{ __('hrm.not_recorded') }}'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-black/55 dark:text-white/55">{{ __('hrm.clock_out_label') }}</span>
                                <span class="font-bold tabular-nums text-black dark:text-white" x-text="selectedCorrection?.attendance?.clock_out_at ? selectedCorrection.attendance.clock_out_at.substring(11, 16) + ' WIB' : '{{ __('hrm.not_recorded') }}'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 rounded-[16px] bg-[#007AFF]/[0.06] border border-[#007AFF]/20 space-y-2.5">
                        <div class="flex items-center gap-2 text-[#007AFF] font-bold text-[12.5px]">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                            <span>{{ __('hrm.proposed_staff_fix') }}</span>
                        </div>
                        <div class="space-y-1.5 text-[12px]">
                            <div class="flex justify-between">
                                <span class="text-black/55 dark:text-white/55">{{ __('hrm.proposed_in') }}</span>
                                <span class="font-bold tabular-nums text-[#007AFF]" x-text="selectedCorrection?.proposed_clock_in ? selectedCorrection.proposed_clock_in.substring(0, 5) + ' WIB' : '-'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-black/55 dark:text-white/55">{{ __('hrm.proposed_out') }}</span>
                                <span class="font-bold tabular-nums text-[#007AFF]" x-text="selectedCorrection?.proposed_clock_out ? selectedCorrection.proposed_clock_out.substring(0, 5) + ' WIB' : '-'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alasan Staf -->
                <div class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                    <span class="text-[11.5px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 block mb-1">{{ __('hrm.staff_reason_label') }}</span>
                    <p class="text-[13px] text-black dark:text-white leading-relaxed italic font-medium" x-text="selectedCorrection?.reason"></p>
                </div>

                <!-- Lampiran Bukti File -->
                <template x-if="selectedCorrection?.attachment_path">
                    <div class="flex items-center justify-between p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <i data-lucide="paperclip" class="w-4.5 h-4.5 text-[#007AFF] shrink-0"></i>
                            <span class="text-[12.5px] font-bold text-black/80 dark:text-white/80 truncate">{{ __('hrm.attachment_evidence_attached') }}</span>
                        </div>
                        <a :href="'/storage/' + selectedCorrection.attachment_path" target="_blank"
                            class="h-8 px-3.5 rounded-[9px] text-[11.5px] font-bold bg-[#007AFF] text-white hover:bg-[#0071E3] transition flex items-center gap-1.5 shrink-0">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            <span>{{ __('hrm.btn_open_file') }}</span>
                        </a>
                    </div>
                </template>

                <!-- Review Form Controls jika Pending -->
                <template x-if="selectedCorrection?.status === 'pending'">
                    <div class="pt-3 border-t border-black/5 dark:border-white/10 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <form method="POST" :action="'{{ url('hrm/attendance/corrections') }}/' + selectedCorrection?.id + '/approve'"
                                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                                class="space-y-2">
                                @csrf
                                <input type="text" name="review_notes" placeholder="{{ __('hrm.approval_notes_ph') }}"
                                    class="w-full h-11 sm:h-9 px-3.5 sm:px-3 rounded-[10px] sm:rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12px] font-medium text-black dark:text-white focus:ring-1 focus:ring-[#34C759]">
                                <button type="submit" :disabled="isSubmittingForm"
                                    :class="isSubmittingForm ? 'opacity-60 cursor-not-allowed' : ''"
                                    class="w-full h-11 sm:h-10 rounded-[11px] bg-[#34C759] hover:bg-[#28A745] text-white text-[14px] sm:text-[13px] font-bold flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(52,199,89,0.3)] transition active:scale-[0.98] cursor-pointer">
                                    <template x-if="!isSubmittingForm">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                            <span>{{ __('hrm.btn_approve_and_sync') }}</span>
                                        </div>
                                    </template>
                                    <template x-if="isSubmittingForm">
                                        <div class="flex items-center gap-2">
                                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span>{{ __('hrm.processing') }}</span>
                                        </div>
                                    </template>
                                </button>
                            </form>

                            <form method="POST" :action="'{{ url('hrm/attendance/corrections') }}/' + selectedCorrection?.id + '/reject'"
                                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                                class="space-y-2">
                                @csrf
                                <input type="text" name="reason" required minlength="3" placeholder="{{ __('hrm.rejection_reason_ph') }}"
                                    class="w-full h-11 sm:h-9 px-3.5 sm:px-3 rounded-[10px] sm:rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12px] font-medium text-black dark:text-white focus:ring-1 focus:ring-[#FF3B30]">
                                <button type="submit" :disabled="isSubmittingForm"
                                    :class="isSubmittingForm ? 'opacity-60 cursor-not-allowed' : ''"
                                    class="w-full h-11 sm:h-10 rounded-[11px] bg-[#FF3B30] hover:bg-[#D70015] text-white text-[14px] sm:text-[13px] font-bold flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(255,59,48,0.3)] transition active:scale-[0.98] cursor-pointer">
                                    <template x-if="!isSubmittingForm">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="x" class="w-4 h-4"></i>
                                            <span>{{ __('hrm.btn_reject_request') }}</span>
                                        </div>
                                    </template>
                                    <template x-if="isSubmittingForm">
                                        <div class="flex items-center gap-2">
                                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span>{{ __('hrm.processing') }}</span>
                                        </div>
                                    </template>
                                </button>
                            </form>
                        </div>
                    </div>
                </template>
            </div>
                </div>
            </div>
        </div>
    </template>

    <!-- ======================================================== -->
    <!-- MODAL SHIFT 1: TAMBAH SHIFT KERJA (APPLE HIG)            -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showAddShiftModal" 
            x-cloak
            @keydown.escape.window="showAddShiftModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showAddShiftModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showAddShiftModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showAddShiftModal = false">
                <div x-show="showAddShiftModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_32px_64px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="px-6 py-4.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#30B0C7]/15 text-[#30B0C7] flex items-center justify-center shrink-0">
                                <i data-lucide="clock" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.modal_add_shift_title') }}</h3>
                                <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.modal_add_shift_sub') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showAddShiftModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <form method="POST" action="{{ route('hrm.shifts.store') }}"
                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                class="p-6 overflow-y-auto space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div class="sm:col-span-2">
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_shift_name') }} <span class="text-[#FF3B30]">*</span></label>
                        <input type="text" name="name" required placeholder="{{ __('hrm.shift_name_placeholder') }}"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[14px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#30B0C7]/40">
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_shift_code') }}</label>
                        <input type="text" name="code" placeholder="{{ __('hrm.shift_code_placeholder') }}" maxlength="20"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[14px] font-mono font-semibold text-black dark:text-white uppercase focus:ring-2 focus:ring-[#30B0C7]/40">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_start_time') }} <span class="text-[#FF3B30]">*</span></label>
                        <input type="time" name="start_time" required value="08:00"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[15px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#30B0C7]/40">
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_end_time') }} <span class="text-[#FF3B30]">*</span></label>
                        <input type="time" name="end_time" required value="17:00"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[15px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#30B0C7]/40">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_late_grace') }} <span class="text-[#FF3B30]">*</span></label>
                        <select name="grace_period_minutes" class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#30B0C7]/40">
                            <option value="0">{{ __('hrm.opt_grace_0') }}</option>
                            <option value="5">{{ __('hrm.opt_grace_5') }}</option>
                            <option value="10" selected>{{ __('hrm.opt_grace_10') }}</option>
                            <option value="15">{{ __('hrm.opt_grace_15') }}</option>
                            <option value="30">{{ __('hrm.opt_grace_30') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_break_duration') }}</label>
                        <div class="relative">
                            <input type="number" name="break_duration_minutes" value="60" min="0" max="240"
                                class="w-full h-11 px-3.5 pr-12 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13.5px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#30B0C7]/40">
                            <span class="absolute right-3.5 top-3 text-xs text-black/40 dark:text-white/40 font-medium">{{ __('hrm.unit_minutes') }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_branch_location') }}</label>
                        <select name="location_id" class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#30B0C7]/40">
                            <option value="">{{ __('hrm.all_locations_global') }}</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Overnight Shift Toggle -->
                <div class="p-3.5 rounded-[14px] bg-[#AF52DE]/[0.06] border border-[#AF52DE]/20 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-[#AF52DE]/15 text-[#AF52DE] flex items-center justify-center shrink-0">
                            <i data-lucide="moon" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-black dark:text-white block">{{ __('hrm.field_overnight_shift') }}</span>
                            <span class="text-[11px] text-black/60 dark:text-white/60 block">{{ __('hrm.field_overnight_hint') }}</span>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_overnight" value="1" class="sr-only peer">
                        <div class="w-11 h-6 bg-black/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#AF52DE]"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_shift_description') }}</label>
                    <textarea name="description" rows="2" placeholder="{{ __('hrm.shift_notes_placeholder') }}"
                        class="w-full p-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white"></textarea>
                </div>

                <div class="pt-3 border-t border-black/5 dark:border-white/10 flex items-center justify-end gap-2.5">
                    <button type="button" @click="showAddShiftModal = false"
                        class="h-11 px-4.5 rounded-[12px] text-xs font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                        {{ __('hrm.btn_cancel') }}
                    </button>
                    <button type="submit" :disabled="isSubmittingForm"
                        class="h-11 px-5 rounded-[12px] bg-[#30B0C7] hover:bg-[#2591a3] text-white text-xs font-bold transition active:scale-[0.98] shadow-sm cursor-pointer">
                        {{ __('hrm.btn_save_shift') }}
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>
    </template>

    <!-- ======================================================== -->
    <!-- MODAL SHIFT 2: EDIT SHIFT KERJA (APPLE HIG)              -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showEditShiftModal" 
            x-cloak
            @keydown.escape.window="showEditShiftModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showEditShiftModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showEditShiftModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showEditShiftModal = false">
                <div x-show="showEditShiftModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_32px_64px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="px-6 py-4.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                                <i data-lucide="edit-3" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.modal_edit_shift_title') }}</h3>
                                <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.modal_edit_shift_sub') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showEditShiftModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <form method="POST" :action="editShiftFormAction"
                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                class="p-6 overflow-y-auto space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div class="sm:col-span-2">
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_shift_name') }} <span class="text-[#FF3B30]">*</span></label>
                        <input type="text" name="name" required :value="selectedShift?.name || ''"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[14px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/40">
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_shift_code') }}</label>
                        <input type="text" name="code" :value="selectedShift?.code || ''" maxlength="20"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[14px] font-mono font-semibold text-black dark:text-white uppercase focus:ring-2 focus:ring-[#007AFF]/40">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_start_time') }} <span class="text-[#FF3B30]">*</span></label>
                        <input type="time" name="start_time" required :value="selectedShift?.start_time || ''"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[15px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/40">
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_end_time') }} <span class="text-[#FF3B30]">*</span></label>
                        <input type="time" name="end_time" required :value="selectedShift?.end_time || ''"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[15px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/40">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_late_grace') }} <span class="text-[#FF3B30]">*</span></label>
                        <select name="grace_period_minutes" class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/40">
                            <option value="0" :selected="selectedShift?.grace_period_minutes === 0">{{ __('hrm.opt_grace_0') }}</option>
                            <option value="5" :selected="selectedShift?.grace_period_minutes === 5">{{ __('hrm.opt_grace_5') }}</option>
                            <option value="10" :selected="selectedShift?.grace_period_minutes === 10">{{ __('hrm.opt_grace_10_plain') }}</option>
                            <option value="15" :selected="selectedShift?.grace_period_minutes === 15">{{ __('hrm.opt_grace_15') }}</option>
                            <option value="30" :selected="selectedShift?.grace_period_minutes === 30">{{ __('hrm.opt_grace_30') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_break_duration') }}</label>
                        <div class="relative">
                            <input type="number" name="break_duration_minutes" :value="selectedShift?.break_duration_minutes || 0" min="0" max="240"
                                class="w-full h-11 px-3.5 pr-12 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13.5px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/40">
                            <span class="absolute right-3.5 top-3 text-xs text-black/40 dark:text-white/40 font-medium">{{ __('hrm.unit_minutes') }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_branch_location') }}</label>
                        <select name="location_id" class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/40">
                            <option value="">{{ __('hrm.all_locations_global') }}</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" :selected="selectedShift?.location_id === '{{ $loc->id }}'">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Overnight Shift Toggle -->
                <div class="p-3.5 rounded-[14px] bg-[#AF52DE]/[0.06] border border-[#AF52DE]/20 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-[#AF52DE]/15 text-[#AF52DE] flex items-center justify-center shrink-0">
                            <i data-lucide="moon" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-black dark:text-white block">{{ __('hrm.field_overnight_shift') }}</span>
                            <span class="text-[11px] text-black/60 dark:text-white/60 block">{{ __('hrm.field_overnight_hint') }}</span>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_overnight" value="1" :checked="!!selectedShift?.is_overnight" class="sr-only peer">
                        <div class="w-11 h-6 bg-black/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#AF52DE]"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_shift_description') }}</label>
                    <textarea name="description" rows="2" :value="selectedShift?.description || ''"
                        class="w-full p-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white"></textarea>
                </div>

                <div class="pt-3 border-t border-black/5 dark:border-white/10 flex items-center justify-end gap-2.5">
                    <button type="button" @click="showEditShiftModal = false"
                        class="h-11 px-4.5 rounded-[12px] text-xs font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                        {{ __('hrm.btn_cancel') }}
                    </button>
                    <button type="submit" :disabled="isSubmittingForm"
                        class="h-11 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-bold transition active:scale-[0.98] shadow-sm cursor-pointer">
                        {{ __('hrm.btn_update_shift') }}
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>
    </template>

    <!-- ======================================================== -->
    <!-- MODAL ROSTER 1: TAMBAH JADWAL / ROSTER (APPLE HIG)       -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showAddScheduleModal" 
            x-cloak
            @keydown.escape.window="showAddScheduleModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showAddScheduleModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showAddScheduleModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showAddScheduleModal = false">
                <div x-show="showAddScheduleModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_32px_64px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="px-6 py-4.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#5856D6]/15 text-[#5856D6] flex items-center justify-center shrink-0">
                                <i data-lucide="calendar" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.modal_add_roster_title') }}</h3>
                                <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.modal_add_roster_sub') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showAddScheduleModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <form method="POST" action="{{ route('hrm.schedules.store') }}"
                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                class="p-6 overflow-y-auto space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_employee_required') }} <span class="text-[#FF3B30]">*</span></label>
                        <select name="user_id" required class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                            @foreach($memberships as $m)
                                @if($m->user)
                                    <option value="{{ $m->user->id }}">{{ $m->user->name }} ({{ $m->roleModel?->name ?? $m->role }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_schedule_type') }} <span class="text-[#FF3B30]">*</span></label>
                        <select name="schedule_type" x-model="scheduleType" class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                            <option value="recurring">{{ __('hrm.opt_weekly_pattern') }}</option>
                            <option value="specific_date">{{ __('hrm.opt_specific_date_event') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Conditional Day of Week / Specific Date -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <template x-if="scheduleType === 'recurring'">
                        <div>
                            <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_work_day') }} <span class="text-[#FF3B30]">*</span></label>
                            <select name="day_of_week" class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                                <option value="monday">{{ __('hrm.day_monday') }}</option>
                                <option value="tuesday">{{ __('hrm.day_tuesday') }}</option>
                                <option value="wednesday">{{ __('hrm.day_wednesday') }}</option>
                                <option value="thursday">{{ __('hrm.day_thursday') }}</option>
                                <option value="friday">{{ __('hrm.day_friday') }}</option>
                                <option value="saturday">{{ __('hrm.day_saturday') }}</option>
                                <option value="sunday">{{ __('hrm.day_sunday') }}</option>
                            </select>
                        </div>
                    </template>
                    <template x-if="scheduleType === 'specific_date'">
                        <div>
                            <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_specific_date_req') }} <span class="text-[#FF3B30]">*</span></label>
                            <input type="date" name="specific_date" value="{{ date('Y-m-d') }}"
                                class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                        </div>
                    </template>

                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.th_shift_assignment') }}</label>
                        <select name="work_shift_id" :disabled="isOffDay" class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40 disabled:opacity-40">
                            @foreach($workShifts as $ws)
                                <option value="{{ $ws->id }}">{{ $ws->name }} ({{ $ws->start_time }} - {{ $ws->end_time }}{{ $ws->isOvernight() ? ' +1' : '' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- OFF DAY Toggle -->
                <div class="p-3.5 rounded-[14px] bg-[#FF3B30]/[0.06] border border-[#FF3B30]/20 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-[#FF3B30]/15 text-[#FF3B30] flex items-center justify-center shrink-0">
                            <i data-lucide="coffee" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-black dark:text-white block">{{ __('hrm.field_off_day') }}</span>
                            <span class="text-[11px] text-black/60 dark:text-white/60 block">{{ __('hrm.field_off_day_hint') }}</span>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_off_day" value="1" x-model="isOffDay" class="sr-only peer">
                        <div class="w-11 h-6 bg-black/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#FF3B30]"></div>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_effective_from') }}</label>
                        <input type="date" name="effective_date" placeholder="{{ __('hrm.effective_date_ph') }}"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                        <span class="text-[10px] text-black/50 dark:text-white/50 block mt-1">{{ __('hrm.field_shift_change_hint') }}</span>
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_effective_until') }}</label>
                        <input type="date" name="end_date" placeholder="{{ __('hrm.end_date_ph') }}"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                    </div>
                </div>

                <div>
                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_override_location') }}</label>
                    <select name="location_id" class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                        <option value="">{{ __('hrm.opt_follow_placement') }}</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_roster_notes') }}</label>
                    <input type="text" name="notes" placeholder="{{ __('hrm.roster_notes_placeholder') }}"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
                </div>

                <div class="pt-3 border-t border-black/5 dark:border-white/10 flex items-center justify-end gap-2.5">
                    <button type="button" @click="showAddScheduleModal = false"
                        class="h-11 px-4.5 rounded-[12px] text-xs font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                        {{ __('hrm.btn_cancel') }}
                    </button>
                    <button type="submit" :disabled="isSubmittingForm"
                        class="h-11 px-5 rounded-[12px] bg-[#5856D6] hover:bg-[#4745B8] text-white text-xs font-bold transition active:scale-[0.98] shadow-sm cursor-pointer">
                        {{ __('hrm.btn_save_roster') }}
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>
    </template>

    <!-- ======================================================== -->
    <!-- MODAL ROSTER 2: EDIT JADWAL / ROSTER (APPLE HIG)         -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showEditScheduleModal" 
            x-cloak
            @keydown.escape.window="showEditScheduleModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showEditScheduleModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showEditScheduleModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showEditScheduleModal = false">
                <div x-show="showEditScheduleModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_32px_64px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="px-6 py-4.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#5856D6]/15 text-[#5856D6] flex items-center justify-center shrink-0">
                                <i data-lucide="edit-3" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.modal_edit_roster_title') }}</h3>
                                <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.modal_edit_roster_sub') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showEditScheduleModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <form method="POST" :action="editScheduleFormAction"
                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                class="p-6 overflow-y-auto space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_employee_required') }} <span class="text-[#FF3B30]">*</span></label>
                        <select name="user_id" required class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                            @foreach($memberships as $m)
                                @if($m->user)
                                    <option value="{{ $m->user->id }}" :selected="selectedSchedule?.user_id === '{{ $m->user->id }}'">{{ $m->user->name }} ({{ $m->roleModel?->name ?? $m->role }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_schedule_type') }} <span class="text-[#FF3B30]">*</span></label>
                        <select name="schedule_type" x-model="scheduleType" class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                            <option value="recurring" :selected="selectedSchedule?.schedule_type === 'recurring'">{{ __('hrm.opt_weekly_pattern_short') }}</option>
                            <option value="specific_date" :selected="selectedSchedule?.schedule_type === 'specific_date'">{{ __('hrm.opt_specific_date_short') }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <template x-if="scheduleType === 'recurring'">
                        <div>
                            <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_work_day') }} <span class="text-[#FF3B30]">*</span></label>
                            <select name="day_of_week" class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                                <option value="monday" :selected="selectedSchedule?.day_of_week === 'monday'">{{ __('hrm.day_monday') }}</option>
                                <option value="tuesday" :selected="selectedSchedule?.day_of_week === 'tuesday'">{{ __('hrm.day_tuesday') }}</option>
                                <option value="wednesday" :selected="selectedSchedule?.day_of_week === 'wednesday'">{{ __('hrm.day_wednesday') }}</option>
                                <option value="thursday" :selected="selectedSchedule?.day_of_week === 'thursday'">{{ __('hrm.day_thursday') }}</option>
                                <option value="friday" :selected="selectedSchedule?.day_of_week === 'friday'">{{ __('hrm.day_friday') }}</option>
                                <option value="saturday" :selected="selectedSchedule?.day_of_week === 'saturday'">{{ __('hrm.day_saturday') }}</option>
                                <option value="sunday" :selected="selectedSchedule?.day_of_week === 'sunday'">{{ __('hrm.day_sunday') }}</option>
                            </select>
                        </div>
                    </template>
                    <template x-if="scheduleType === 'specific_date'">
                        <div>
                            <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_specific_date_req') }} <span class="text-[#FF3B30]">*</span></label>
                            <input type="date" name="specific_date" :value="selectedSchedule?.specific_date ? selectedSchedule.specific_date.substring(0, 10) : ''"
                                class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                        </div>
                    </template>

                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.th_shift_assignment') }}</label>
                        <select name="work_shift_id" :disabled="isOffDay" class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40 disabled:opacity-40">
                            @foreach($workShifts as $ws)
                                <option value="{{ $ws->id }}" :selected="selectedSchedule?.work_shift_id === '{{ $ws->id }}'">{{ $ws->name }} ({{ $ws->start_time }} - {{ $ws->end_time }}{{ $ws->isOvernight() ? ' +1' : '' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- OFF DAY Toggle -->
                <div class="p-3.5 rounded-[14px] bg-[#FF3B30]/[0.06] border border-[#FF3B30]/20 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-[#FF3B30]/15 text-[#FF3B30] flex items-center justify-center shrink-0">
                            <i data-lucide="coffee" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-black dark:text-white block">{{ __('hrm.field_off_day') }}</span>
                            <span class="text-[11px] text-black/60 dark:text-white/60 block">{{ __('hrm.field_off_day_hint') }}</span>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_off_day" value="1" x-model="isOffDay" class="sr-only peer">
                        <div class="w-11 h-6 bg-black/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#FF3B30]"></div>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_effective_from') }}</label>
                        <input type="date" name="effective_date" :value="selectedSchedule?.effective_date ? selectedSchedule.effective_date.substring(0, 10) : ''"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_effective_until') }}</label>
                        <input type="date" name="end_date" :value="selectedSchedule?.end_date ? selectedSchedule.end_date.substring(0, 10) : ''"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                    </div>
                </div>

                <div>
                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_override_location') }}</label>
                    <select name="location_id" class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                        <option value="">{{ __('hrm.opt_follow_placement') }}</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" :selected="selectedSchedule?.location_id === '{{ $loc->id }}'">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_roster_notes') }}</label>
                    <input type="text" name="notes" :value="selectedSchedule?.notes || ''"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
                </div>

                <div class="pt-3 border-t border-black/5 dark:border-white/10 flex items-center justify-end gap-2.5">
                    <button type="button" @click="showEditScheduleModal = false"
                        class="h-11 px-4.5 rounded-[12px] text-xs font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                        {{ __('hrm.btn_cancel') }}
                    </button>
                    <button type="submit" :disabled="isSubmittingForm"
                        class="h-11 px-5 rounded-[12px] bg-[#5856D6] hover:bg-[#4745B8] text-white text-xs font-bold transition active:scale-[0.98] shadow-sm cursor-pointer">
                        {{ __('hrm.btn_update_roster') }}
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>
    </template>


    <!-- ======================================================== -->
    <!-- MODAL IMPORT KARYAWAN MASSAL (BENTO XXL PRE-COMMIT)       -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showImportEmployeeModal" 
            x-cloak
            @keydown.escape.window="if (!importEmployeeExecuting) showImportEmployeeModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showImportEmployeeModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="if (!importEmployeeExecuting) showImportEmployeeModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-4" @click.self="showImportEmployeeModal = false">
                <div x-show="showImportEmployeeModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-5xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_32px_64px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]">

                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-white dark:bg-[#1C1C1E]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                <i data-lucide="users" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-black dark:text-white">{{ __('hrm.modal_import_emp_title') }}</h3>
                                <p class="text-xs text-black/55 dark:text-white/55">{{ __('hrm.modal_import_emp_sub') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="if (!importEmployeeExecuting) showImportEmployeeModal = false"
                            class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto space-y-6 flex-1">

                <!-- State 1: Upload Dropzone -->
                <div x-show="!importEmployeePreview" class="space-y-4">
                    <!-- Langkah 1: Unduh Template -->
                    <div class="p-4 rounded-[16px] bg-[#007AFF]/[0.05] dark:bg-[#007AFF]/[0.1] border border-[#007AFF]/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-[#007AFF] font-bold text-[13px]">
                                <span class="w-5 h-5 rounded-full bg-[#007AFF]/15 flex items-center justify-center text-[11px] font-bold">1</span>
                                <span>{{ __('hrm.step_1_download_emp_template') }}</span>
                            </div>
                            <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.step_1_emp_template_desc') }}</p>
                        </div>
                        <a href="{{ route('hrm.employees.template') }}"
                           class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0066D6] text-white text-[12px] font-semibold transition flex items-center justify-center gap-1.5 shadow-sm shrink-0 active:scale-[0.98]">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span>{{ __('hrm.btn_download_template_xlsx') }}</span>
                        </a>
                    </div>

                    <!-- Langkah 2: Upload Dropzone -->
                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center gap-2 text-black/80 dark:text-white/80 font-bold text-[13px]">
                            <span class="w-5 h-5 rounded-full bg-black/10 dark:bg-white/10 flex items-center justify-center text-[11px] font-bold">2</span>
                            <span>{{ __('hrm.step_2_upload_emp_file') }}</span>
                        </div>
                        <span class="text-[11px] text-black/40 dark:text-white/40">{{ __('hrm.max_file_size_10mb') }}</span>
                    </div>

                    <label class="border-2 border-dashed border-black/15 dark:border-white/15 hover:border-[#007AFF] dark:hover:border-[#007AFF] rounded-[20px] p-8 sm:p-12 flex flex-col items-center justify-center text-center cursor-pointer transition bg-black/[0.01] hover:bg-[#007AFF]/[0.02] block">
                        <input type="file" accept=".xlsx,.xls,.csv" @change="handleImportEmployeeFileSelect($event)" class="hidden">
                        
                        <div class="w-16 h-16 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mb-4 mx-auto">
                            <i data-lucide="file-spreadsheet" class="w-8 h-8" x-show="!importEmployeeLoading"></i>
                            <i data-lucide="loader" class="w-8 h-8 animate-spin" x-show="importEmployeeLoading"></i>
                        </div>

                        <div class="space-y-1">
                            <span class="text-base font-bold text-black dark:text-white block" x-text="importEmployeeLoading ? '{{ addslashes(__('hrm.upload_emp_file_analyzing')) }}' : '{{ addslashes(__('hrm.upload_emp_file_idle')) }}'"></span>
                            <span class="text-xs text-black/50 dark:text-white/50 block">{{ __('hrm.upload_emp_format_hint') }}</span>
                        </div>
                    </label>
                </div>

                <!-- State 2: Pre-Commit Preview -->
                <div x-show="importEmployeePreview" class="space-y-5">
                    <!-- KPI Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1">
                            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('hrm.metric_total_staff') }}</span>
                            <div class="text-2xl font-bold font-mono text-black dark:text-white" x-text="importEmployeePreview ? importEmployeePreview.total_rows : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-emerald-500/[0.08] border border-emerald-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">{{ __('hrm.metric_ready_import') }}</span>
                            <div class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400" x-text="importEmployeePreview ? importEmployeePreview.valid_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-amber-500/[0.08] border border-amber-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">{{ __('hrm.metric_duplicate') }}</span>
                            <div class="text-2xl font-bold font-mono text-amber-600 dark:text-amber-400" x-text="importEmployeePreview ? importEmployeePreview.duplicate_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-rose-500/[0.08] border border-rose-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-rose-700 dark:text-rose-400 uppercase tracking-wider">{{ __('hrm.metric_format_error') }}</span>
                            <div class="text-2xl font-bold font-mono text-rose-600 dark:text-rose-400" x-text="importEmployeePreview ? importEmployeePreview.error_count : 0"></div>
                        </div>
                    </div>

                    <!-- Filter & Duplicate Strategy Bar -->
                    <div class="p-4 rounded-[18px] bg-[#F5F5F7] dark:bg-[#252528] border border-black/[0.05] dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-1 bg-black/[0.05] dark:bg-white/[0.08] p-1 rounded-[12px] text-[12px] font-medium">
                            <button type="button" @click="importEmployeeFilter = 'all'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importEmployeeFilter === 'all' ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60'">
                                {{ __('hrm.filter_all') }} (<span x-text="importEmployeePreview ? importEmployeePreview.total_rows : 0"></span>)
                            </button>
                            <button type="button" @click="importEmployeeFilter = 'valid'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importEmployeeFilter === 'valid' ? 'bg-white dark:bg-[#1C1C1E] text-emerald-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                {{ __('hrm.filter_ready') }} (<span x-text="importEmployeePreview ? importEmployeePreview.valid_count : 0"></span>)
                            </button>
                            <button type="button" @click="importEmployeeFilter = 'duplicate'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importEmployeeFilter === 'duplicate' ? 'bg-white dark:bg-[#1C1C1E] text-amber-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                {{ __('hrm.filter_duplicate') }} (<span x-text="importEmployeePreview ? importEmployeePreview.duplicate_count : 0"></span>)
                            </button>
                            <button type="button" @click="importEmployeeFilter = 'error'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importEmployeeFilter === 'error' ? 'bg-white dark:bg-[#1C1C1E] text-rose-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                {{ __('hrm.filter_error') }} (<span x-text="importEmployeePreview ? importEmployeePreview.error_count : 0"></span>)
                            </button>
                        </div>

                        <div class="flex items-center gap-3 text-[12px]">
                            <span class="text-black/50 dark:text-white/50 font-medium">{{ __('hrm.dup_strategy_emp_label') }}</span>
                            <label class="flex items-center gap-1.5 cursor-pointer font-medium text-black dark:text-white">
                                <input type="radio" value="skip" x-model="importEmployeeDuplicateStrategy" class="text-[#007AFF] focus:ring-0">
                                <span>{{ __('hrm.dup_strategy_skip') }}</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer font-medium text-black dark:text-white">
                                <input type="radio" value="update" x-model="importEmployeeDuplicateStrategy" class="text-[#007AFF] focus:ring-0">
                                <span>{{ __('hrm.dup_strategy_update') }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Interactive Table -->
                    <div class="border border-black/[0.08] dark:border-white/[0.1] rounded-[18px] overflow-hidden bg-white dark:bg-[#1C1C1E]">
                        <div class="max-h-[380px] overflow-y-auto">
                            <table class="w-full text-left text-[12px] border-collapse min-w-[800px]">
                                <thead class="sticky top-0 bg-[#F5F5F7] dark:bg-[#252528] border-b border-black/[0.08] dark:border-white/[0.1] text-black/60 dark:text-white/60 font-semibold z-10">
                                    <tr>
                                        <th class="py-2.5 px-3 w-16 text-center">{{ __('hrm.th_status') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_employee_name') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_email_phone') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_role_position') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_work_type') }}</th>
                                        <th class="py-2.5 px-3 text-right">{{ __('hrm.th_basic_salary') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_branch_location') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_notes_message') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                                    <template x-for="(row, idx) in (importEmployeePreview ? importEmployeePreview.rows.filter(r => importEmployeeFilter === 'all' || r.status === importEmployeeFilter) : [])" :key="idx">
                                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                                            <td class="py-2.5 px-3 text-center">
                                                <span x-show="row.status === 'valid'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">{{ __('hrm.filter_ready') }}</span>
                                                <span x-show="row.status === 'duplicate'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 border border-amber-500/20">{{ __('hrm.filter_duplicate') }}</span>
                                                <span x-show="row.status === 'error'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-600 border border-rose-500/20">{{ __('hrm.filter_error') }}</span>
                                            </td>
                                            <td class="py-2.5 px-3 font-semibold text-black dark:text-white" x-text="row.name || '-'"></td>
                                            <td class="py-2.5 px-3 text-black/70 dark:text-white/70">
                                                <div x-text="row.email || '-'"></div>
                                                <div class="text-[11px] text-black/40 dark:text-white/40" x-text="row.phone || ''"></div>
                                            </td>
                                            <td class="py-2.5 px-3 text-black/70 dark:text-white/70">
                                                <div class="font-medium text-black dark:text-white" x-text="row.job_title || '-'"></div>
                                                <div class="text-[11px] text-black/50 dark:text-white/50 capitalize" x-text="'Role: ' + (row.role || 'staff')"></div>
                                            </td>
                                            <td class="py-2.5 px-3 text-black/70 dark:text-white/70 capitalize" x-text="row.employment_type || 'full_time'"></td>
                                            <td class="py-2.5 px-3 text-right font-mono font-semibold text-black dark:text-white" x-text="'Rp ' + formatCurrency(row.base_salary)"></td>
                                            <td class="py-2.5 px-3 text-black/70 dark:text-white/70" x-text="row.location_name || '{{ addslashes(__('hrm.default_hq_location')) }}'"></td>
                                            <td class="py-2.5 px-3">
                                                <div class="space-y-0.5">
                                                    <template x-for="(msg, mi) in row.messages" :key="mi">
                                                        <div class="text-[11px]" :class="row.status === 'error' ? 'text-rose-600 dark:text-rose-400' : (row.status === 'duplicate' ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400')" x-text="msg"></div>
                                                    </template>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-[#F9F9FB] dark:bg-[#252528]">
                <div>
                    <button type="button" x-show="importEmployeePreview" @click="resetEmployeeImport()"
                        class="h-10 px-4 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold transition cursor-pointer flex items-center gap-1.5"
                        :disabled="importEmployeeExecuting">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        <span>{{ __('hrm.btn_change_excel_file') }}</span>
                    </button>
                </div>

                <div class="flex items-center gap-2.5">
                    <button type="button" @click="if (!importEmployeeExecuting) showImportEmployeeModal = false"
                        class="h-10 px-5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold transition cursor-pointer"
                        :disabled="importEmployeeExecuting">
                        {{ __('hrm.btn_cancel') }}
                    </button>

                    <button type="button" x-show="importEmployeePreview" @click="executeEmployeeImport()"
                        class="h-10 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold shadow-sm shadow-[#007AFF]/25 active:scale-[0.98] transition cursor-pointer flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="importEmployeeExecuting || !importEmployeePreview || (importEmployeeDuplicateStrategy === 'skip' ? importEmployeePreview.valid_count : (importEmployeePreview.valid_count + importEmployeePreview.duplicate_count)) === 0">
                        <i data-lucide="loader" class="w-4 h-4 animate-spin" x-show="importEmployeeExecuting"></i>
                        <i data-lucide="check" class="w-4 h-4" x-show="!importEmployeeExecuting"></i>
                        <span x-text="importEmployeeExecuting ? '{{ addslashes(__('hrm.processing_import')) }}' : ('{{ addslashes(__('hrm.commit_import_prefix')) }}' + (importEmployeeDuplicateStrategy === 'skip' ? importEmployeePreview.valid_count : (importEmployeePreview.valid_count + importEmployeePreview.duplicate_count)) + '{{ addslashes(__('hrm.commit_import_suffix_employees')) }}')"></span>
                    </button>
                </div>
                </div>
            </div>
        </div>
    </template>

    <!-- ======================================================== -->
    <!-- MODAL IMPORT PRESENSI MESIN (BENTO XXL PRE-COMMIT)        -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showImportAttendanceModal" 
            x-cloak
            @keydown.escape.window="if (!importAttendanceExecuting) showImportAttendanceModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showImportAttendanceModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="if (!importAttendanceExecuting) showImportAttendanceModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-4" @click.self="showImportAttendanceModal = false">
                <div x-show="showImportAttendanceModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-5xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_32px_64px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]">

                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-white dark:bg-[#1C1C1E]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                                <i data-lucide="fingerprint" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-black dark:text-white">{{ __('hrm.modal_import_att_title') }}</h3>
                                <p class="text-xs text-black/55 dark:text-white/55">{{ __('hrm.modal_import_att_sub') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="if (!importAttendanceExecuting) showImportAttendanceModal = false"
                            class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto space-y-6 flex-1">

                <!-- State 1: Upload Dropzone -->
                <div x-show="!importAttendancePreview" class="space-y-4">
                    <!-- Langkah 1: Unduh Template -->
                    <div class="p-4 rounded-[16px] bg-[#34C759]/[0.08] dark:bg-[#34C759]/[0.12] border border-[#34C759]/25 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-[#248A3D] dark:text-[#30D158] font-bold text-[13px]">
                                <span class="w-5 h-5 rounded-full bg-[#34C759]/15 flex items-center justify-center text-[11px] font-bold">1</span>
                                <span>{{ __('hrm.step_1_download_att_template') }}</span>
                            </div>
                            <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.step_1_att_template_desc') }}</p>
                        </div>
                        <a href="{{ route('hrm.attendance.template') }}"
                           class="h-9 px-4 rounded-[10px] bg-[#34C759] hover:bg-[#28A745] text-white text-[12px] font-semibold transition flex items-center justify-center gap-1.5 shadow-sm shrink-0 active:scale-[0.98]">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span>{{ __('hrm.btn_download_template_xlsx') }}</span>
                        </a>
                    </div>

                    <!-- Langkah 2: Upload Dropzone -->
                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center gap-2 text-black/80 dark:text-white/80 font-bold text-[13px]">
                            <span class="w-5 h-5 rounded-full bg-black/10 dark:bg-white/10 flex items-center justify-center text-[11px] font-bold">2</span>
                            <span>{{ __('hrm.step_2_upload_att_file') }}</span>
                        </div>
                        <span class="text-[11px] text-black/40 dark:text-white/40">{{ __('hrm.max_file_size_10mb') }}</span>
                    </div>

                    <label class="border-2 border-dashed border-black/15 dark:border-white/15 hover:border-[#34C759] dark:hover:border-[#34C759] rounded-[20px] p-8 sm:p-12 flex flex-col items-center justify-center text-center cursor-pointer transition bg-black/[0.01] hover:bg-[#34C759]/[0.02] block">
                        <input type="file" accept=".xlsx,.xls,.csv" @change="handleImportAttendanceFileSelect($event)" class="hidden">
                        
                        <div class="w-16 h-16 rounded-full bg-[#34C759]/10 text-[#34C759] flex items-center justify-center mb-4 mx-auto">
                            <i data-lucide="file-spreadsheet" class="w-8 h-8" x-show="!importAttendanceLoading"></i>
                            <i data-lucide="loader" class="w-8 h-8 animate-spin" x-show="importAttendanceLoading"></i>
                        </div>

                        <div class="space-y-1">
                            <span class="text-base font-bold text-black dark:text-white block" x-text="importAttendanceLoading ? '{{ addslashes(__('hrm.upload_att_file_analyzing')) }}' : '{{ addslashes(__('hrm.upload_att_file_idle')) }}'"></span>
                            <span class="text-xs text-black/50 dark:text-white/50 block">{{ __('hrm.upload_att_format_hint') }}</span>
                        </div>
                    </label>
                </div>

                <!-- State 2: Pre-Commit Preview -->
                <div x-show="importAttendancePreview" class="space-y-5">
                    <!-- KPI Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                        <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1">
                            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('hrm.metric_total_rows') }}</span>
                            <div class="text-2xl font-bold font-mono text-black dark:text-white" x-text="importAttendancePreview ? importAttendancePreview.total_rows : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-emerald-500/[0.08] border border-emerald-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">{{ __('hrm.metric_ready_import') }}</span>
                            <div class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400" x-text="importAttendancePreview ? importAttendancePreview.valid_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-amber-500/[0.08] border border-amber-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">{{ __('hrm.metric_already_exists') }}</span>
                            <div class="text-2xl font-bold font-mono text-amber-600 dark:text-amber-400" x-text="importAttendancePreview ? importAttendancePreview.duplicate_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-rose-500/[0.08] border border-rose-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-rose-700 dark:text-rose-400 uppercase tracking-wider">{{ __('hrm.metric_format_error') }}</span>
                            <div class="text-2xl font-bold font-mono text-rose-600 dark:text-rose-400" x-text="importAttendancePreview ? importAttendancePreview.error_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-[#34C759]/[0.08] border border-[#34C759]/20 space-y-1 col-span-2 sm:col-span-1">
                            <span class="text-[11px] font-semibold text-[#34C759] uppercase tracking-wider">{{ __('hrm.metric_total_work_hours') }}</span>
                            <div class="text-xl font-bold font-mono text-[#34C759]" x-text="importAttendancePreview ? (importAttendancePreview.total_work_hours + ' {{ addslashes(__('hrm.unit_hours')) }}') : '0 {{ addslashes(__('hrm.unit_hours')) }}'"></div>
                        </div>
                    </div>

                    <!-- Filter & Duplicate Strategy Bar -->
                    <div class="p-4 rounded-[18px] bg-[#F5F5F7] dark:bg-[#252528] border border-black/[0.05] dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-1 bg-black/[0.05] dark:bg-white/[0.08] p-1 rounded-[12px] text-[12px] font-medium">
                            <button type="button" @click="importAttendanceFilter = 'all'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importAttendanceFilter === 'all' ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60'">
                                {{ __('hrm.filter_all') }} (<span x-text="importAttendancePreview ? importAttendancePreview.total_rows : 0"></span>)
                            </button>
                            <button type="button" @click="importAttendanceFilter = 'valid'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importAttendanceFilter === 'valid' ? 'bg-white dark:bg-[#1C1C1E] text-emerald-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                {{ __('hrm.filter_ready') }} (<span x-text="importAttendancePreview ? importAttendancePreview.valid_count : 0"></span>)
                            </button>
                            <button type="button" @click="importAttendanceFilter = 'duplicate'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importAttendanceFilter === 'duplicate' ? 'bg-white dark:bg-[#1C1C1E] text-amber-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                {{ __('hrm.filter_duplicate') }} (<span x-text="importAttendancePreview ? importAttendancePreview.duplicate_count : 0"></span>)
                            </button>
                            <button type="button" @click="importAttendanceFilter = 'error'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importAttendanceFilter === 'error' ? 'bg-white dark:bg-[#1C1C1E] text-rose-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                {{ __('hrm.filter_error') }} (<span x-text="importAttendancePreview ? importAttendancePreview.error_count : 0"></span>)
                            </button>
                        </div>

                        <div class="flex items-center gap-3 text-[12px]">
                            <span class="text-black/50 dark:text-white/50 font-medium">{{ __('hrm.dup_strategy_att_label') }}</span>
                            <label class="flex items-center gap-1.5 cursor-pointer font-medium text-black dark:text-white">
                                <input type="radio" value="skip" x-model="importAttendanceDuplicateStrategy" class="text-[#34C759] focus:ring-0">
                                <span>{{ __('hrm.dup_strategy_skip') }}</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer font-medium text-black dark:text-white">
                                <input type="radio" value="update" x-model="importAttendanceDuplicateStrategy" class="text-[#34C759] focus:ring-0">
                                <span>{{ __('hrm.dup_strategy_update_hours') }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Interactive Table -->
                    <div class="border border-black/[0.08] dark:border-white/[0.1] rounded-[18px] overflow-hidden bg-white dark:bg-[#1C1C1E]">
                        <div class="max-h-[380px] overflow-y-auto">
                            <table class="w-full text-left text-[12px] border-collapse min-w-[750px]">
                                <thead class="sticky top-0 bg-[#F5F5F7] dark:bg-[#252528] border-b border-black/[0.08] dark:border-white/[0.1] text-black/60 dark:text-white/60 font-semibold z-10">
                                    <tr>
                                        <th class="py-2.5 px-3 w-16 text-center">{{ __('hrm.th_status') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_employee') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_date') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_clock_in') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_clock_out') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_tardiness') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_work_duration') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_attendance_status') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_diagnostic') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                                    <template x-for="(row, idx) in (importAttendancePreview ? importAttendancePreview.rows.filter(r => importAttendanceFilter === 'all' || r.status === importAttendanceFilter) : [])" :key="idx">
                                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                                            <td class="py-2.5 px-3 text-center">
                                                <span x-show="row.status === 'valid'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">{{ __('hrm.filter_ready') }}</span>
                                                <span x-show="row.status === 'duplicate'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 border border-amber-500/20">{{ __('hrm.filter_duplicate') }}</span>
                                                <span x-show="row.status === 'error'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-600 border border-rose-500/20">{{ __('hrm.filter_error') }}</span>
                                            </td>
                                            <td class="py-2.5 px-3 font-semibold text-black dark:text-white">
                                                <div x-text="row.user_name || row.identifier"></div>
                                                <div class="text-[11px] text-black/40 dark:text-white/40 font-mono" x-text="row.identifier"></div>
                                            </td>
                                            <td class="py-2.5 px-3 tabular-nums text-black/80 dark:text-white/80" x-text="row.date || '-'"></td>
                                            <td class="py-2.5 px-3 font-mono font-bold text-emerald-600 dark:text-emerald-400" x-text="row.clock_in ? row.clock_in.substring(11, 16) : '-'"></td>
                                            <td class="py-2.5 px-3 font-mono font-bold text-black/70 dark:text-white/70" x-text="row.clock_out ? row.clock_out.substring(11, 16) : '-'"></td>
                                            <td class="py-2.5 px-3 tabular-nums" :class="row.late_minutes > 0 ? 'text-amber-600 dark:text-amber-400 font-bold' : 'text-black/50 dark:text-white/50'" x-text="row.late_minutes > 0 ? (row.late_minutes + ' {{ addslashes(__('hrm.unit_minutes_short')) }}') : '{{ addslashes(__('hrm.status_on_time')) }}'"></td>
                                            <td class="py-2.5 px-3 tabular-nums font-medium text-black dark:text-white" x-text="Math.floor((row.work_duration_minutes || 0) / 60) + 'j ' + ((row.work_duration_minutes || 0) % 60) + 'm'"></td>
                                            <td class="py-2.5 px-3">
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                                    :class="row.attendance_status === 'late' ? 'bg-amber-500/10 text-amber-600' : 'bg-emerald-500/10 text-emerald-600'"
                                                    x-text="row.attendance_status === 'late' ? '{{ addslashes(__('hrm.status_late')) }}' : '{{ addslashes(__('hrm.status_present')) }}'"></span>
                                            </td>
                                            <td class="py-2.5 px-3">
                                                <div class="space-y-0.5">
                                                    <template x-for="(msg, mi) in row.messages" :key="mi">
                                                        <div class="text-[11px]" :class="row.status === 'error' ? 'text-rose-600 dark:text-rose-400' : (row.status === 'duplicate' ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400')" x-text="msg"></div>
                                                    </template>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-[#F9F9FB] dark:bg-[#252528]">
                <div>
                    <button type="button" x-show="importAttendancePreview" @click="resetAttendanceImport()"
                        class="h-10 px-4 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold transition cursor-pointer flex items-center gap-1.5"
                        :disabled="importAttendanceExecuting">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        <span>{{ __('hrm.btn_change_excel_file') }}</span>
                    </button>
                </div>

                <div class="flex items-center gap-2.5">
                    <button type="button" @click="if (!importAttendanceExecuting) showImportAttendanceModal = false"
                        class="h-10 px-5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold transition cursor-pointer"
                        :disabled="importAttendanceExecuting">
                        {{ __('hrm.btn_cancel') }}
                    </button>

                    <button type="button" x-show="importAttendancePreview" @click="executeAttendanceImport()"
                        class="h-10 px-6 rounded-[12px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-[13px] font-semibold shadow-sm shadow-[#34C759]/25 active:scale-[0.98] transition cursor-pointer flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="importAttendanceExecuting || !importAttendancePreview || (importAttendanceDuplicateStrategy === 'skip' ? importAttendancePreview.valid_count : (importAttendancePreview.valid_count + importAttendancePreview.duplicate_count)) === 0">
                        <i data-lucide="loader" class="w-4 h-4 animate-spin" x-show="importAttendanceExecuting"></i>
                        <i data-lucide="check" class="w-4 h-4" x-show="!importAttendanceExecuting"></i>
                        <span x-text="importAttendanceExecuting ? '{{ addslashes(__('hrm.processing_import')) }}' : ('{{ addslashes(__('hrm.commit_import_prefix')) }}' + (importAttendanceDuplicateStrategy === 'skip' ? importAttendancePreview.valid_count : (importAttendancePreview.valid_count + importAttendancePreview.duplicate_count)) + '{{ addslashes(__('hrm.commit_import_suffix_attendance')) }}')"></span>
                    </button>
                </div>
                </div>
            </div>
        </div>
    </template>

</div>
@endsection
