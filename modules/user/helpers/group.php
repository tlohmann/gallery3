<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * This is the API for handling groups.
 *
 * Note: by design, this class does not do any permission checking.
 */
class group_Core {
  /**
   * @see Identity_Driver::create.
   */
  static function create($name) {
    $group = ORM::factory("group")->where("name", $name)->find();
    if ($group->loaded) {
      throw new Exception("@todo GROUP_ALREADY_EXISTS $name");
    }

    $group->name = $name;
    $group->save();
    return $group;
  }

  /**
   * @see Identity_Driver::everbody.
   */
  static function everybody() {
    return model_cache::get("group", 1);
  }

  /**
   * @see Identity_Driver::registered_users.
   */
  static function registered_users() {
    return model_cache::get("group", 2);
  }

  /**
   * Look up a group by id.
   * @param integer      $id the user id
   * @return Group_Definition  the group object, or null if the id was invalid.
   */
  static function lookup($id) {
    return self::lookup_by_field("id", $id);
  }

  /**
   * Look up a group by name.
   * @param integer      $id the group name
   * @return Group_Definition  the group object, or null if the name was invalid.
   */
  static function lookup_by_name($name) {
    return self::lookup_by_field("name", $name);
  }

  /**
   * @see Identity_Driver::get_group_list.
   */
  static function lookup_by_field($field_name, $value) {
    try {
      $user = model_cache::get("group", $value, $field_name);
      if ($user->loaded) {
        return $user;
      }
    } catch (Exception $e) {
      if (strpos($e->getMessage(), "MISSING_MODEL") === false) {
       throw $e;
      }
    }
    return null;
  }
}
