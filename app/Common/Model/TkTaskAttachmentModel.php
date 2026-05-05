<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class TkTaskAttachmentModel extends Model
{
    use SoftDeletes;

    protected ?string $table = 'tk_task_attachment';
    
    public bool $timestamps = false;

    protected array $guarded = [];

    
    public function task()
    {
        return $this->belongsTo(TkTaskModel::class, 'task_id', 'id');
    }
    
    public function uploader()
    {
        return $this->belongsTo(TkUserModel::class, 'uploader_id', 'id');
    }
}
