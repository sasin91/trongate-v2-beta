<?php

/**
 * Provides functionalities related to managing comments within the admin panel.
 */
class Trongate_comments extends Trongate {

    /**
     * @var string The default system'username for comments from a user id of 0.
     */
    private $zero_id_username = 'System';

    /**
     * Prepare comments data with formatted dates and user information.
     * This method is typically called via admin.js.
     *
     * @param array $output The output data containing comments.
     * @return array Processed output data with formatted comments.
     */
    public function _prep_comments(array $output): array {

        // Extract comments body from output
        $body = $output['body'];

        //get an array of all trongate_administrators
        $sql = 'SELECT trongate_users.id, trongate_administrators.username 
                FROM trongate_comments 
                INNER JOIN trongate_users ON trongate_comments.user_id = trongate_users.id 
                INNER JOIN trongate_administrators ON trongate_users.id = trongate_administrators.trongate_user_id';
        $all_admins = $this->model->query($sql, 'object');

        $admin_users = [];
        foreach ($all_admins as $admin_user) {
            $admin_users[$admin_user->id] = $admin_user->username;
        }

        $comments = json_decode($body);
        $data = [];
        foreach ($comments as $key => $value) {
            $row_data['comment'] = nl2br($value->comment);

            if (isset($admin_users[$value->user_id])) {
                $posted_by = $admin_users[$value->user_id];
            } else {

                if (($value->user_id === 0) && (isset($this->zero_id_username))) {
                    $posted_by = $this->zero_id_username;
                } else {
                    $posted_by = 'an unknown user';
                }
            }

            $date_created = date('l jS \of F Y \a\t h:i:s A', $value->date_created);
            $row_data['date_created'] = 'Posted by ' . $posted_by . ' on ' . $date_created;
            $row_data['user_id'] = $value->user_id;
            $row_data['target_table'] = $value->target_table;
            $row_data['update_id'] = $value->update_id;
            $row_data['code'] = $value->code;
            $data[] = $row_data;
        }

        $output['body'] = json_encode($data);
        return $output;
    }

}