<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
class Login_Controller extends Controller {

  public function ajax() {
    $view = new View("login_ajax.html");
    $view->form = auth::get_login_form("login/auth_ajax");
    print $view;
  }

  public function auth_ajax() {
    access::verify_csrf();

    list ($valid, $form) = $this->_auth("login/auth_ajax");
    if ($valid) {
      print json_encode(
        array("result" => "success"));
    } else {
      print json_encode(array("result" => "error", "form" => (string) $form));
    }
  }

  public function html() {
    print auth::get_login_form("login/auth_html");
  }

  public function auth_html() {
    access::verify_csrf();

    $continue_url = Session::instance()->get("continue_url", null);
    list ($valid, $form) = $this->_auth("login/auth_html");
    if ($valid) {
      url::redirect($continue_url ? $continue_url : item::root()->abs_url());
    } else {
      $view = new Theme_View("page.html", "other", "login");
      $view->page_title = t("Log in to Gallery");
      $view->content = new View("login_ajax.html");
      $view->content->form = $form;
      print $view;
    }
  }

  private function _auth($url) {
    $form = auth::get_login_form($url);
    $valid = $form->validate();
    if ($valid) {
      $user = identity::lookup_user_by_name($form->login->inputs["name"]->value);
      if (empty($user) || !identity::is_correct_password($user, $form->login->password->value)) {
        $form->login->inputs["name"]->add_error("invalid_login", 1);
        $name = $form->login->inputs["name"]->value;
        log::warning("user", t("Failed login for %name", array("name" => $name)));
        module::event("user_auth_failed", $name);
        $valid = false;
      }
    }

    if ($valid) {
      auth::login($user);
    }

    // Either way, regenerate the session id to avoid session trapping
    Session::instance()->regenerate();

    return array($valid, $form);
  }
}