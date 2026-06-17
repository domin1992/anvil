<?php

namespace Anvil\Jobs;

class PublishMissedSchedulePosts extends Job
{
    protected function setInterval()
    {
        $this->hourly();
    }

    public function handle()
    {
        global $wpdb;

        $missed_schedule_post_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_status = %s AND post_date < %s",
                'future',
                gmdate('Y-m-d H:i:s')
            )
        );

        if (count($missed_schedule_post_ids)) {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->posts} SET post_status = %s WHERE ID IN (%s)",
                    'publish',
                    implode(',', $missed_schedule_post_ids)
                )
            );

            echo sprintf('Updated %d posts', count($missed_schedule_post_ids));
        }

        echo 'No missed posts';
    }
}
