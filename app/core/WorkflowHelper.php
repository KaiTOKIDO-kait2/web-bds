<?php
/**
 * WorkflowHelper.php
 * 
 * Helper functions để dịch và hiển thị các trạng thái workflow
 * mới (case_status, appointment_status)
 */

class WorkflowHelper
{

    /**
     * Lấy label tiếng Việt cho case_status
     * 
     * @param string $caseStatus Status value (new|contacted|scheduled|viewed|completed|cancelled)
     * @return string Human-readable label
     */
    public static function getCaseStatusLabel($caseStatus)
    {
        $caseStatus = strtolower((string) $caseStatus);
        $map = [
            'new' => 'Mới',
            'contacted' => 'Đã tiếp nhận',
            'scheduled' => 'Đã hẹn lịch',
            'viewed' => 'Đã xem nhà',
            'completed' => 'Hoàn tất',
            'cancelled' => 'Không thành công',
        ];
        return isset($map[$caseStatus]) ? $map[$caseStatus] : $caseStatus;
    }

    /**
     * Lấy badge CSS class cho case_status
     * 
     * @param string $caseStatus Status value
     * @return string Bootstrap badge class (badge-primary, badge-success, etc.)
     */
    public static function getCaseStatusBadgeClass($caseStatus)
    {
        $caseStatus = strtolower((string) $caseStatus);
        $map = [
            'new' => 'badge-secondary',
            'contacted' => 'badge-info',
            'scheduled' => 'badge-primary',
            'viewed' => 'badge-info',
            'completed' => 'badge-success',
            'cancelled' => 'badge-danger',
        ];
        return isset($map[$caseStatus]) ? $map[$caseStatus] : 'badge-secondary';
    }

    /**
     * Lấy label tiếng Việt cho appointment_status
     * 
     * @param string $appointmentStatus Status value (none|pending|confirmed|completed|cancelled)
     * @return string Human-readable label
     */
    public static function getAppointmentStatusLabel($appointmentStatus)
    {
        $appointmentStatus = strtolower((string) $appointmentStatus);
        $map = [
            'none' => 'Chưa đặt',
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'completed' => 'Đã xem nhà',
            'cancelled' => 'Đã hủy',
        ];
        return isset($map[$appointmentStatus]) ? $map[$appointmentStatus] : $appointmentStatus;
    }

    /**
     * Lấy badge CSS class cho appointment_status
     * 
     * @param string $appointmentStatus Status value
     * @return string Bootstrap badge class
     */
    public static function getAppointmentStatusBadgeClass($appointmentStatus)
    {
        $appointmentStatus = strtolower((string) $appointmentStatus);
        $map = [
            'none' => 'badge-secondary',
            'pending' => 'badge-warning',
            'confirmed' => 'badge-primary',
            'completed' => 'badge-success',
            'cancelled' => 'badge-danger',
        ];
        return isset($map[$appointmentStatus]) ? $map[$appointmentStatus] : 'badge-secondary';
    }

    /**
     * Lấy label tiếng Việt cho inquiry status
     * 
     * @param string $status Status value (pending|accepted|rejected)
     * @return string Human-readable label
     */
    public static function getInquiryStatusLabel($status)
    {
        $status = strtolower((string) $status);
        $map = [
            'pending' => 'Chờ xác nhận',
            'accepted' => 'Đã tiếp nhận',
            'rejected' => 'Đã từ chối',
        ];
        return isset($map[$status]) ? $map[$status] : $status;
    }

    /**
     * Lấy badge CSS class cho inquiry status
     * 
     * @param string $status Status value
     * @return string Bootstrap badge class
     */
    public static function getInquiryStatusBadgeClass($status)
    {
        $status = strtolower((string) $status);
        $map = [
            'pending' => 'badge-warning',
            'accepted' => 'badge-success',
            'rejected' => 'badge-danger',
        ];
        return isset($map[$status]) ? $map[$status] : 'badge-secondary';
    }

    /**
     * Lấy label tiếng Việt cho property status
     * 
     * @param string $propertyStatus Status value (available|in_progress|rented|inactive)
     * @return string Human-readable label
     */
    public static function getPropertyStatusLabel($propertyStatus)
    {
        $propertyStatus = strtolower((string) $propertyStatus);
        $map = [
            'available' => 'Còn trống',
            'in_progress' => 'Đang xử lý',
            'rented' => 'Đã cho thuê',
            'inactive' => 'Không hoạt động',
        ];
        return isset($map[$propertyStatus]) ? $map[$propertyStatus] : $propertyStatus;
    }

    /**
     * Lấy badge CSS class cho property status
     * 
     * @param string $propertyStatus Status value
     * @return string Bootstrap badge class
     */
    public static function getPropertyStatusBadgeClass($propertyStatus)
    {
        $propertyStatus = strtolower((string) $propertyStatus);
        $map = [
            'available' => 'badge-success',
            'in_progress' => 'badge-info',
            'rented' => 'badge-primary',
            'inactive' => 'badge-secondary',
        ];
        return isset($map[$propertyStatus]) ? $map[$propertyStatus] : 'badge-secondary';
    }

    /**
     * Lấy tất cả các label dạng array
     * Tiện dụng cho việc fill select dropdown
     * 
     * @return array
     */
    public static function getAllLabels()
    {
        return [
            'case_status' => [
                'new' => 'Mới',
                'contacted' => 'Đã tiếp nhận',
                'scheduled' => 'Đã hẹn lịch',
                'viewed' => 'Đã xem nhà',
                'completed' => 'Hoàn tất',
                'cancelled' => 'Không thành công',
            ],
            'appointment_status' => [
                'none' => 'Chưa đặt',
                'pending' => 'Chờ xác nhận',
                'confirmed' => 'Đã xác nhận',
                'completed' => 'Đã xem nhà',
                'cancelled' => 'Đã hủy',
            ],
            'inquiry_status' => [
                'pending' => 'Chờ xác nhận',
                'accepted' => 'Đã tiếp nhận',
                'rejected' => 'Đã từ chối',
            ],
            'property_status' => [
                'available' => 'Còn trống',
                'in_progress' => 'Đang xử lý',
                'rented' => 'Đã cho thuê',
                'inactive' => 'Không hoạt động',
            ],
        ];
    }
}
