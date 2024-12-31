<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SecurityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'user_agent',
        'event_type',
        'description',
        'user_id',
        'route',
        'request_data',
        'severity',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
    ];

    protected $casts = [
        'request_data' => 'array',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    // العلاقة مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // الحصول على لون نوع الحدث
    public function getEventTypeColorAttribute()
    {
        return match($this->event_type) {
            'login' => 'success',
            'logout' => 'info',
            'failed_login' => 'danger',
            'password_reset' => 'warning',
            'profile_update' => 'primary',
            'settings_change' => 'secondary',
            default => 'dark'
        };
    }

    // الحصول على حالة السجل
    public function getStatusAttribute()
    {
        return $this->is_resolved ? 'تم الحل' : 'قيد المعالجة';
    }

    // Scopes للبحث والتصفية
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByEventType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeByIpAddress($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
