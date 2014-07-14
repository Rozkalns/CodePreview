<?php

/**
 * User class
 *
 * Here is just one method that shows algorithm for updating user. Might look dirty
 * but works as needed. Of corse, there are some things, that I don't like, so I
 * hope that client gives more support for upgrades and tuning.
 *
 * @copyright  2012 Roberts Rozkalns
 * @version    Release: @1.3.2@
 * @since      Class available since Release 0.1
 */

class User {

    /**
     * Update User data
     *
     * @param  array    $data  Array of data that should be updated
     * @param  integer  $id The ID of user, that Admin might change
     * @return bool
     */
    function update_member($data, $id = NULL) {
        // check if method caller is logged in
        if (!is_logged_in()) {
            return false;
        }

        // Input names in front end are called different than in database
        $names = array(
            'id' => 'id',
            'user_pass' => 'password',
            'user_email' => 'email',
            'user_registered' => 'time_register',
            'user_activation_key' => 'activation_key',
            'user_status' => 'status',
            'user_display_name' => 'full_name',
            'user_name' => 'name',
            'user_surname' => 'surname',
            'user_gender' => 'gender',
            'user_phone' => 'mobile',
            'input_location_city' => 'city',
            'input_location_address' => 'street',
            'input_coordinates' => 'coordinates',
            'user_image' => 'image',
            'user_work' => 'work',
            'user_study' => 'study',
            'user_exp' => 'experience',
            'user_role' => 'role',
            'user_last_activity' => 'last_activity'
        );

        // Admin might call this method and edit data of other user than himself
        if ($id) {
            $current_user = user($id);
        } else {
            $current_user = current_user();
        }

        // Fields that sould be ignored and not troubled
        $ignore_fields = array('user_save', 'user_pass_check', 'user_pass_re', 'user_terms');

        // Fields that are not stored in users metadata table
        $meta_ignore_fields = array('user_email', 'user_pass');

        // Fields that are stored in main database
        $main_update_fields = array('user_pass', 'user_email', 'user_name', 'user_surname');

        // Store fields in array that sould be updated
        $main_update = array();

        //$n = 0;
        foreach ($data as $k => $v) {

            // Skip fields that should be ignored
            if (in_array($k, $ignore_fields)) {
                continue;
            }

            // Fields that are in main table and pending for update procedure
            if (in_array($k, $main_update_fields)) {
                $main_update[$k] = $v;
            }

            // Skip fields that should be ignored for meta table
            if (in_array($k, $meta_ignore_fields)) {
                continue;
            }

            // User object consist of variables and data and are checked against
            // stored information in database between given information from
            // form
            // In this step we insert, update or delete Meta data
            // If user has something stored in DB
            if ($current_user->$names[$k] != NULL) {

                // If new form information is different
                if ($current_user->$names[$k] != $v) {

                    // If given information is empty
                    if ($v == "") {
                        // Then delete information from DB with this key
                        $this->delete_member_meta($current_user->id, array($k => $v));
                    } else {
                        // Or is different, then find and update DB with this key
                        $this->update_member_meta($current_user->id, array($k => $v));
                    }
                }
            }

            // User dont have anything stored with this key
            else {

                // Make sure if given input is not empty
                if ($v != "") {
                    // Then insert information in DB with this key
                    $this->insert_member_meta($current_user->id, array($k => $v));
                }
            }
        }

        // In this step we insert, update or delete Main data
        // Check if Main update array is filled
        if (!empty($main_update)) {

            // Update display Name
            if (isset($main_update['user_name']) && isset($main_update['user_surname']) && $current_user->full_name != $main_update['user_name'] . " " . $main_update['user_surname']) {
                $this->update_member_main($current_user->id, array('user_display_name' => $main_update['user_name'] . " " . $main_update['user_surname']));
            }

            // Update user Password
            if (isset($main_update['user_pass']) && $current_user->password != $this->hash($main_update['user_pass'])) {
                $this->update_member_main($current_user->id, array('user_pass' => $this->hash($main_update['user_pass'])));
            }

            // Update user email and update at the same time Session data, to keep user logged 
            if (isset($main_update['user_email']) && $current_user->email != $data['user_email']) {
                $this->update_member_main($current_user->id, array('user_email' => $main_update['user_email']));
                $this->session->set_userdata(array('user_email' => $data['user_email']));
            }
        }

        return false;
    }

}
