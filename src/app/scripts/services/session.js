'use strict';

/**
 * @ngdoc service
 * @name srcApp.Session
 * @description
 * # Session
 * Service in the srcApp.
 */
angular.module('srcApp')
  .service('Session', function () {
    this.create = function (id, username) {
      this.id = id;
      this.username = username;
    };
    this.destroy = function () {
      this.id = null;
      this.username = null;
    };
    return this;
  });
