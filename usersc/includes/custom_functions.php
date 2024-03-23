<?php
/*
UserSpice 5
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//Put your custom functions in this file and they will be automatically included.

//bold("<br><br>custom helpers included");

// Singleton class for Redis connection
class RedisSingleton {
    private static $instance;
    private $redis;

    private function __construct() {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance->redis;
    }
}

// Centralized function for Redis operations
if (!function_exists('redisOperation')) {
function redisOperation($key, $value = null, $expiration = null) {
    $redis = RedisSingleton::getInstance();
    if ($value !== null) {
        // Set value in Redis
        $redis->set($key, $value);
        if ($expiration !== null) {
            // Set expiration time if provided
            $redis->expire($key, $expiration);
        }
    } else {
        // Get value from Redis
        return $redis->get($key);
    }
}
}

///

//UserSpice User-Related functions
//Do not deactivate!

// Check if a user ID exists in the DB
// Check if a user ID exists in the DB
if (!function_exists('userIdExists')) {
    function userIdExists($id)
    {
        $cached_result = redisOperation('user_id_exists_' . $id);
        if ($cached_result !== false) {
            return $cached_result == 1;
        }

        $db = DB::getInstance();
        $query = $db->query('SELECT * FROM users WHERE id = ?', [$id]);
        $num_returns = $query->count();

        redisOperation('user_id_exists_' . $id, $num_returns == 1 ? 1 : 0);

        return $num_returns == 1;
    }
}

// Retrieve information for all users
if (!function_exists('fetchAllUsers')) {
    function fetchAllUsers($orderBy = null, $desc = false, $disabled = true)
    {
        $cached_result = redisOperation('all_users');
        if ($cached_result !== false) {
            return unserialize($cached_result);
        }

        $db = DB::getInstance();
        $q = 'SELECT * FROM users';
        if (!$disabled) {
            $q .= ' WHERE permissions=1';
        }
        if ($orderBy !== null) {
            if ($desc === true) {
                $q .= " ORDER BY $orderBy DESC";
            } else {
                $q .= " ORDER BY $orderBy";
            }
        }
        $query = $db->query($q);
        $results = $query->results();

        redisOperation('all_users', serialize($results));

        return $results;
    }
}

// Retrieve complete user info by user ID
if (!function_exists('fetchUser')) {
    function fetchUser($id)
    {
        $cached_result = redisOperation('user_' . $id);
        if ($cached_result !== false) {
            return unserialize($cached_result);
        }

        $db = DB::getInstance();
        $query = $db->query("SELECT * FROM users WHERE id = ?", [$id]);
        if ($query->count() > 0) {
            $result = $query->first();
            redisOperation('user_' . $id, serialize($result));
            return $result;
        } else {
            return false;
        }
    }
}

// Retrieve complete user information by username, token, or user ID
if (!function_exists('fetchUserDetails')) {
    function fetchUserDetails($column = null, $term = null, $id = null)
    {
        $cached_result = redisOperation('user_details_' . $term);
        if ($cached_result !== false) {
            return unserialize($cached_result);
        }

        $db = DB::getInstance();
        if ($column == null || $column == "") {
            $column = "id";
        }

        if ($term == null || $term == "") {
            $term = $id;
        }

        $query = $db->query("SELECT * FROM users WHERE $column = ? LIMIT 1", [$term]);
        if ($query->count() == 1) {
            $result = $query->first();
            redisOperation('user_details_' . $term, serialize($result));
            return $result;
        } else {
            return false;
        }
    }
}

// Delete a defined array of users
if (!function_exists('deleteUsers')) {
    function deleteUsers($users)
    {
        global $abs_us_root, $us_url_root;
        $db = DB::getInstance();
        $i = 0;
        foreach ($users as $id) {
            $query1 = $db->query('DELETE FROM users WHERE id = ?', [$id]);
            $query2 = $db->query('DELETE FROM user_permission_matches WHERE user_id = ?', [$id]);
            if (file_exists($abs_us_root . $us_url_root . 'usersc/scripts/after_user_deletion.php')) {
                include $abs_us_root . $us_url_root . 'usersc/scripts/after_user_deletion.php';
            }
            ++$i;
        }

        return $i;
    }
}

// Check if the user is an admin
if (!function_exists('isAdmin')) {
    function isAdmin()
    {
        if (hasPerm(2) || (isset($_SESSION['cloak_from']) && hasPerm(2, $_SESSION['cloak_from']))) {
            return true;
        } else {
            return false;
        }
    }
}

// Get username by ID
if (!function_exists('name_from_id')) {
    function name_from_id($id)
    {
        $cached_result = redisOperation('user_name_' . $id);
        if ($cached_result !== false) {
            return unserialize($cached_result);
        }

        $db = DB::getInstance();
        $query = $db->query('SELECT username FROM users WHERE id = ? LIMIT 1', [$id]);
        $count = $query->count();
        if ($count > 0) {
            $result = $query->first();
            redisOperation('user_name_' . $id, serialize($result));
            return ucfirst($result->username);
        } else {
            return '-';
        }
    }
}
