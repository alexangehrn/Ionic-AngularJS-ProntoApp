// Ionic Starter App

// angular.module is a global place for creating, registering and retrieving Angular modules
// 'starter' is the name of this angular module example (also set in a <body> attribute in index.html)
// the 2nd parameter is an array of 'requires'
// 'starter.controllers' is found in controllers.js
angular.module('starter', ['ionic', 'starter.controllers'])


.run(function($ionicPlatform) {
  $ionicPlatform.ready(function() {
    // Hide the accessory bar by default (remove this to show the accessory bar above the keyboard
    // for form inputs)
    if (window.cordova && window.cordova.plugins.Keyboard) {
      cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);
      cordova.plugins.Keyboard.disableScroll(true);

    }
    if (window.StatusBar) {
      // org.apache.cordova.statusbar required
      StatusBar.styleDefault();
    }
  });
})

.config(function($stateProvider, $urlRouterProvider) {
  $stateProvider

  .state('index', {
    url: '/',
    templateUrl: 'templates/login.html',
    controller: 'AppCtrl'
  })
  .state('home', {
    url: '/home',
    templateUrl: 'templates/home.html',
    controller: 'HomeCtrl'
  })
  .state('seProd', {
    url: '/seProd',
    templateUrl: 'templates/seProd.html',
    controller: 'SECtrl'
  })
  .state('seVin', {
    url: '/seVin',
    templateUrl: 'templates/seVin.html',
    controller: 'SEVCtrl'
  })
  .state('barSProd', {
    url: '/barSProd',
    templateUrl: 'templates/barSProd.html',
    controller: 'barCtrl'
  })
  .state('barEVin', {
    url: '/barEVin',
    templateUrl: 'templates/barEVin.html',
    controller: 'barVCtrl'
  })
  .state('barSVin', {
    url: '/barSVin',
    templateUrl: 'templates/barSVin.html',
    controller: 'barVCtrl'
  })
  .state('barEProd', {
    url: '/barEProd',
    templateUrl: 'templates/barEProd.html',
    controller: 'barCtrl'
  })
  .state('entreeProd', {
    url: '/entreeProd',
    templateUrl: 'templates/entreeProd.html',
    controller: 'prodCtrl'
  })
  .state('entreeVin', {
    url: '/entreeVin',
    templateUrl: 'templates/entreeVin.html',
    controller: 'vinCtrl'
  })
  .state('etiProd', {
    url: '/etiProd',
    templateUrl: 'templates/etiProd.html',
    controller: 'etiCtrl'
  })
  .state('etivProd', {
    url: '/etivProd',
    templateUrl: 'templates/etivProd.html',
    controller: 'etivCtrl'
  })
  .state('comProd', {
    url: '/comProd',
    templateUrl: 'templates/comProd.html',
    controller: 'prodCtrl'
  })
  .state('comVin', {
    url: '/comVin',
    templateUrl: 'templates/comVin.html',
    controller: 'vinCtrl'
  })
  .state('stockProd', {
    url: '/stockProd',
    templateUrl: 'templates/stockProd.html',
    controller: 'prodCtrl'
  })
  .state('stockVin', {
    url: '/stockVin',
    templateUrl: 'templates/stockVin.html',
    controller: 'vinCtrl'
  })
  .state('stockProdDet', {
     url: '/stockProdDet/:prodId',
     templateUrl: 'templates/stockProdDet.html',
    controller: 'prodCtrlDet'
   })
    .state('stockVinDet', {
    url: '/stockVinDet/:vinId',
    templateUrl: 'templates/stockVinDet.html',
    controller: 'vinDetCtrl'
  })
   .state('stockComDet', {
      url: '/stockComDet/:prodId',
      templateUrl: 'templates/stockComDet.html',
     controller: 'comCtrlDet'
    })
    .state('seReci', {
      url: '/seReci',
      templateUrl: 'templates/seReci.html',
      controller: 'reciCtrl'
    })
  .state('listReci', {
    url: '/listReci',
    templateUrl: 'templates/listReci.html',
    controller: 'reciCtrl'
  })
  .state('ventes', {
    url: '/ventes',
    templateUrl: 'templates/ventes.html',
    controller: 'ventesCtrl'
  })
  .state('stats', {
    url: '/stats',
    templateUrl: 'templates/stats.html',
    controller: 'statsCtrl'
  })
  .state('recetteDet', {
     url: '/recetteDet/:recId',
     templateUrl: 'templates/recetteDet.html',
    controller: 'recetteDet'
   })
  .state('addReci', {
    url: '/addReci',
    templateUrl: 'templates/addReci.html',
    controller: 'reciCtrl'
  });
  // if none of the above states are matched, use this as the fallback
    $urlRouterProvider.otherwise('/');
});
