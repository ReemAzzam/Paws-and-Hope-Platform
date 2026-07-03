<?php

namespace App\Services;

use App\Models\LostFound;
use App\Models\LostFoundMatch;
use App\Events\SendNotificationEvent;
use App\Support\NotificationTemplates;

class LostFoundService
{
    protected $matcher;

    public function __construct(LostFoundMatcher $matcher)
    {
        $this->matcher = $matcher;
    }

     // إنشاء منشور جديد (Lost أو Found) + تشغيل المطابقة التلقائية

    public function createPost(array $data): LostFound
    {
        $post = LostFound::create($data);
        // تشغيل المطابقة التلقائية
        $this->runAutoMatching($post);
        return $post;
    }

     // تشغيل المطابقة التلقائية بين المنشور الجديد والمنشورات المعاكسة

    private function runAutoMatching(LostFound $newPost)
    {
        $oppositeType = $newPost->post_type === 'lost' ? 'found' : 'lost';

        $candidates = LostFound::where('post_type', $oppositeType)
            ->where('status', 'open')
            ->where('id', '!=', $newPost->id)
            ->get();

        foreach ($candidates as $candidate) {
            $matchResult = $this->matcher->calculateMatch(
                $newPost->post_type === 'lost' ? $newPost : $candidate,
                $newPost->post_type === 'found' ? $newPost : $candidate
            );
            // إذا كانت المطابقة جيدة (50% فأكثر)
            if ($matchResult['score'] >= 50) {
               $match = LostFoundMatch::create([
                'lost_post_id'  => $newPost->post_type === 'lost'
                    ? $newPost->id
                    : $candidate->id,

                'found_post_id' => $newPost->post_type === 'found'
                    ? $newPost->id
                    : $candidate->id,
                'match_score'   => $matchResult['score'],
                'match_reasons' => $matchResult['reasons'],
                'status'        => 'pending',
                'notified_at'   => now(),
            ]);
            // تحميل العلاقات
            $match->load(['lostPost.user', 'foundPost.user']);
            // إشعار صاحب إعلان المفقود
            // if ($match->lostPost?->user) {
            //     $notification = NotificationTemplates::lostFoundMatch(
            //         $match->foundPost->name ?? 'Animal',
            //         $matchResult['score']
            //     );
            //     SendNotificationEvent::dispatch(
            //         $match->lostPost->user,
            //         $notification['title'],
            //         $notification['body'],
            //         [
            //             'type' => $notification['type'],
            //         ]
            //     );
            // }

            // // إشعار صاحب إعلان الموجود
            // if ($match->foundPost?->user) {

            //     $notification = NotificationTemplates::lostFoundMatch(
            //         $match->lostPost->name ?? 'Animal',
            //         $matchResult['score']
            //     );

            //     SendNotificationEvent::dispatch(
            //         $match->foundPost->user,
            //         $notification['title'],
            //         $notification['body'],
            //         [
            //             'type' => $notification['type'],
            //         ]
            //     );
            // }
        }
    }
    }
}
