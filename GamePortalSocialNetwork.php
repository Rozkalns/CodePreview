<?php

/**
 * Social network class
 *
 * Here are some methods that are used for social network that I created for 
 * social games. Used for displaying the icons and other options and so on.
 * 
 * P.S. All $_POST's ARE cleaned in DB class
 *
 * @copyright  2013 Roberts Rozkalns
 * @version    Release: @1.0@
 * @since      Class available since Release 0.1
 */

class Social {
    
    # Friends related functions ================================================

    /** Determines friendship between current user and given user by id
     * 
     * @param int $id for user to check relation
     * @return string stauts for friendship
     */
    private function determine_friendship($id) {

        $status = NULL;
        
        $friendship_statuses = array(
            'me', 'incoming', 'friend', 'outgoing', 'not_friend'
        );
        
        if ($id == $this->social_user_id) {
            $status = $friendship_statuses[0];
            return $status;
        }

        $selecting_data = array(
            array('friend_id' => $this->social_user_id, 'user_id' => $id, 'approved' => 0),
            array('friend_id' => $this->social_user_id, 'user_id' => $id, 'approved' => 1),
            array('friend_id' => $id, 'user_id' => $this->social_user_id, 'approved' => 0)
        );

        $n = 0;
        foreach ($selecting_data as $data) {
            $q = DB::query_row(db_select('social_friends', '1', $data, 'LIMIT 1'));
            ++$n;
            if ($q) {
                $status = $friendship_statuses[$n];
                return $status;
            }
        }

        if (!$status) {
            $status = $friendship_statuses[4];
        }
        return $status;
    }

    /** Collect limited friendships in one object
     * 
     * @return object
     * @since 0.2
     */
    private function home_get_all_friendships() {
        $relations = array();

        $incoming = DB::query("SELECT id, user_id FROM social_friends WHERE friend_ID = '" . clean_sql($this->social_user_id) . "' AND approved = 0 ORDER BY id DESC LIMIT 0, 12");
        $friends = DB::query("SELECT id, friend_id FROM social_friends WHERE user_ID = '" . clean_sql($this->social_user_id) . "' AND approved = 1 ORDER BY id DESC LIMIT 0, 12");
        $outgoing = DB::query("SELECT id, friend_id FROM social_friends WHERE user_ID = '" . clean_sql($this->social_user_id) . "' AND approved = 0 ORDER BY id DESC LIMIT 0, 12");

        $relations[1] = count($incoming) ? $incoming : false;
        $relations[2] = count($friends) ? $friends : false;
        $relations[3] = count($outgoing) ? $outgoing : false;

        return array_to_object($relations);
    }

    /** Collect all friendships in one object
     * 
     * @return object
     * @since 0.2
     */
    private function get_all_friendships() {
        $relations = array();

        $incoming = DB::query("SELECT id, user_id FROM social_friends WHERE friend_ID = '" . clean_sql($this->social_user_id) . "' AND approved = 0 ORDER BY id ASC");
        $friends = DB::query("SELECT id, friend_id FROM social_friends WHERE user_ID = '" . clean_sql($this->social_user_id) . "' AND approved = 1 ORDER BY id ASC");
        $outgoing = DB::query("SELECT id, friend_id FROM social_friends WHERE user_ID = '" . clean_sql($this->social_user_id) . "' AND approved = 0 ORDER BY id ASC");

        $relations[1] = count($incoming) ? $incoming : false;
        $relations[2] = count($friends) ? $friends : false;
        $relations[3] = count($outgoing) ? $outgoing : false;

        return array_to_object($relations);
    }

    /** Invites friend. Called by Ajax
     * 
     * @return object
     * @since 0.2
     */
    private function invite_friend() {
        $data = array('user_id' => $this->social_user_id, 'friend_id' => $_POST['friend_id'], 'approved' => '0');
        $s = db_insert('social_friends', $data);
        DB::query($s);
        return true;
    }

    /** Accepts friend. Called by Ajax
     * 
     * @return object
     * @since 0.2
     */
    private function accept_friend() {
        DB::query(db_update('social_friends', array('approved' => '1'), array('friend_id' => $this->social_user_id, 'user_id' => $_POST['friend_id'])));
        DB::query(db_insert('social_friends', array('user_id' => $this->social_user_id, 'friend_id' => $_POST['friend_id'], 'approved' => '1')));
        return true;
    }

    /** Unfriend. Called by Ajax
     * 
     * @return object
     * @since 0.2
     */
    private function unfriend() {
        DB::query(db_delete('social_friends', array('friend_id' => $this->social_user_id, 'user_id' => $_POST['friend_id'])));
        DB::query(db_delete('social_friends', array('friend_id' => $_POST['friend_id'], 'user_id' => $this->social_user_id)));
        return true;
    }

    /** Rejects friend. Called by Ajax
     * 
     * @return object
     * @since 0.2
     */
    private function reject_friend() {
        DB::query(db_delete('social_friends', array('friend_id' => $this->social_user_id, 'user_id' => $_POST['friend_id'])));
        return true;
    }

    /** Simplify friendship results to intivation id and friend id
     * 
     * @param string $collecting_query must contain query from social_users table
     * @return object
     * @since 0.2
     */
    private function simplify_friendships($collecting_query) {
        $all_friendships = array();
        $collecting_query = $this->$collecting_query();
        for ($n = 1; $n <= count((array) $collecting_query); $n++) {
            $friend = array();
            $all_fetched_friendships = $collecting_query->{$n};
            if ($all_fetched_friendships) {
                foreach ($all_fetched_friendships as $f) {
                    if ($n == 1) {
                        $friend[$f->id] = $f->user_id;
                    } else {
                        $friend[$f->id] = $f->friend_id;
                    }
                }
                $all_friendships[$n] = $friend;
            } else {
                $all_friendships[$n] = false;
            }
        }
        return array_to_object($all_friendships);
    }
}