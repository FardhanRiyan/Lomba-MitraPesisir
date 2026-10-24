@include('errors.generic', ['code' => 403, 'title' => 'Akses ditolak', 'detail' => $exception->getMessage() ?: 'Anda tidak memiliki akses ke halaman ini.'])
