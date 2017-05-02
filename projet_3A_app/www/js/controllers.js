google.load('visualization', '1', {
  packages: ['corechart']
});

google.setOnLoadCallback(function() {
  angular.bootstrap(document.body, ['starter']);
});

angular.module('starter.controllers', [])

.controller('AppCtrl', function($scope, $http, $ionicModal, $timeout, $state, $ionicLoading) {
  setTimeout(function(){
       document.getElementById("custom-overlay").style.display = "none";
     }, 800);

  var top = (window.innerHeight/2)-200;
  $scope.loginForm= {
    "position" : "relative",
    "top" : top +"px",
  };

  // With the new view caching in Ionic, Controllers are only called
  // when they are recreated or on app start, instead of every page change.
  // To listen for when this page is active (for example, to refresh data),
  // listen for the $ionicView.enter event:
  //$scope.$on('$ionicView.enter', function(e) {
  //});
  if(localStorage.getItem("S-Token") !== null && localStorage.getItem("S-Token") !== ""){
    $state.go('home');
  }

  $scope.home = function() {
    $state.go('home');
  };

  // Form data for the login modal
  $scope.loginData = {};

  // Create the login modal that we will use later
  $ionicModal.fromTemplateUrl('templates/login.html', {
    scope: $scope
  }).then(function(modal) {
    $scope.modal = modal;
  });

  // Triggered in the login modal to close it
  $scope.closeLogin = function($state) {
  //  $location.path('app.browse');
  };


  $scope.doLogout = function() {
    localStorage.setItem("S-Token", "");
    $state.go('index');
  };


  // Perform the login action when the user submits the login form
  $scope.doLogin = function() {

    $http({
         url: 'http://aangehrn.eemi.tech/webservice/index.php/login',
         method: "POST",
         data: $scope.loginData
     }).then(function successCallback(response) {

       $scope.token = response.data;
       localStorage.setItem("S-Token", $scope.token);
       $state.go('home');
       console.log(response);

    }, function errorCallback(error) {
      console.log(error);
    });

  };

})

.controller('HomeCtrl', function($scope, $state) {
  $scope.menu= {
    "height" : window.innerWidth/3+"px",
    "border" : "1px solid #eeeeee"
  };

  $scope.seRedirect = function() {
    $state.go('seProd');
  };

  $scope.statRedirect = function() {
    $state.go('stats');
  };


  $scope.reciRedirect = function() {
    $state.go('seReci');
  };

  $scope.vinRedirect = function() {
    $state.go('seVin');
  };

  $scope.ventesRedirect = function() {
    $state.go('ventes');
  };
})

.controller('SEVCtrl', function($scope, $state) {

  $scope.entree = function() {
        $state.go('barEVin');
  };

  $scope.sortie = function() {
    $state.go('barSVin');
  };

  $scope.commande = function() {
    $state.go('comVin');
  };

  $scope.stock = function() {
    $state.go('stockVin');
  };

})

.controller('SECtrl', function($scope, $state) {

  $scope.entree = function() {
        $state.go('barEProd');
  };

  $scope.sortie = function() {
    $state.go('barSProd');
  };

  $scope.commande = function() {
    $state.go('comProd');
  };

  $scope.stock = function() {
    $state.go('stockProd');
  };

})


.controller('barCtrl', function($scope, $state) {
  $scope.scanS = function() {
    $state.go('home');
  };

  $scope.scanE = function() {
    $state.go('entreeProd');
  };

})

.controller('barVCtrl', function($scope, $state) {
  $scope.scanS = function() {
    $state.go('home');
  };

  $scope.scanE = function() {
    $state.go('entreeVin');
  };

})





.controller('comCtrlDet', function($scope, $http, $state, $stateParams) {
  $scope.token = localStorage.getItem("S-Token");

  $http({
       url: 'http://aangehrn.eemi.tech/webservice/index.php/api/product/detail',
       method: "POST",
      data: $stateParams,
       headers : {
         "S-Token" : $scope.token
       }
   }).then(function successCallback(response) {
     console.log(response.data);

     $scope.product = response.data
  }, function errorCallback(error) {
    //$state.go('etiProd');
    console.log(error);
  });

  $scope.print = function(printSectionId) {
      var innerContents = document.getElementById(printSectionId).innerHTML;
      var popupWinindow = window.open('', '_blank', 'width=600,height=700,scrollbars=no,menubar=no,toolbar=no,location=no,status=no,titlebar=no');
      popupWinindow.document.open();
      popupWinindow.document.write('<html><head><link rel="stylesheet" type="text/css" href="style.css" /></head><body onload="window.print()">' + innerContents + '</html>');
      popupWinindow.document.close();
  };

  $scope.home = function() {
    $state.go('home');
  };
})

.controller('recetteDet', function($scope, $http, $state, $stateParams) {
  $scope.token = localStorage.getItem("S-Token");

  $http({
       url: 'http://aangehrn.eemi.tech/webservice/index.php/api/recette/detail_recette',
       method: "POST",
      data: $stateParams,
       headers : {
         "S-Token" : $scope.token
       }
   }).then(function successCallback(response) {
     console.log(response.data);

     $scope.recette = response.data
  }, function errorCallback(error) {
    //$state.go('etiProd');
    console.log(error);
  });

  $scope.home = function() {
    $state.go('home');
  };
})

.controller('prodCtrl', function($scope, $http, $state, $stateParams) {
  $scope.productData = {};
  $scope.commandData = {};
    $scope.productData.quantite = 1;

  $scope.token = localStorage.getItem("S-Token");

  $http({
       url: 'http://aangehrn.eemi.tech/webservice/index.php/api/productCat/list',
       method: "GET",
       headers : {
         "S-Token" : $scope.token
       }
   }).then(function successCallback(response) {

     $scope.products = response.data
  }, function errorCallback(error) {
    //$state.go('etiProd');
    console.log(error);
  });

  $http({
       url: 'http://aangehrn.eemi.tech/webservice/index.php/api/productFour/list',
       method: "GET",
       headers : {
         "S-Token" : $scope.token
       }
   }).then(function successCallback(response) {
         console.log(response);

     $scope.fourns = response.data
     console.log($scope.fourns);

  }, function errorCallback(error) {
    //$state.go('etiProd');
    console.log(error);
  });


$scope.plus = function() {
  $scope.productData.quantite = $scope.productData.quantite+1
}
$scope.minus = function() {
  $scope.productData.quantite = $scope.productData.quantite-1
}
   $scope.i = 1;

$scope.addInput = function() {
  console.log($scope.i);
   var myEl = angular.element( document.querySelector( '#prodip' ) );
myEl.append('<div class="form-add-product"><div class="margin-left-90 auto topPadding20 text-left"><span class="left size-15 product-form">Nom du Produit</span><span class="left width-130 size-15 quant-form margin-left-35">Quantité</span><span class="left width-130 size-15 poids-form margin-left-35">Poids</span> </div><div class="auto margin-left-90" ><input type="text" class="inputb left product-form" list="prodid" placeholder="Exemple : Tomates" ng-model="commandData.prod['+i+']["produit"]"/><datalist id="prodid"><option ng-repeat="product in products" value="{{product.id_produit}}">{{product.nom_produit}}</option>recipeData.prod['+$scope.i+']["produit_id"]</datalist><input type="number" class="inputb quant-form left margin-left-35" placeholder="Exemple : 20" ng-model="commandData.prod['+$scope.i+']["quantite"]"><input type="number" class="inputb left poids-form margin-left-35" placeholder="Exemple : 500 gr" ng-model="commandData.prod['+$scope.i+']["poids"]"></div>');     
    var i = i+1;
    $scope.i = $scope.i+1;

}

  $scope.enter = function() {
    $http({
         url: 'http://aangehrn.eemi.tech/webservice/index.php/api/product/new_product',
         method: "POST",
         data: $scope.productData,
         headers : {
           "S-Token" : $scope.token
         }
     }).then(function successCallback(response) {
       $state.go('etiProd');
    }, function errorCallback(error) {
      //$state.go('etiProd');
      $state.go('home');
    });
  };





    $scope.order = function() {
      $http({
           url: 'http://aangehrn.eemi.tech/webservice/index.php/api/command/new_command',
           method: "POST",
           data: $scope.commandData,
           headers : {
             "S-Token" : $scope.token
           }
       }).then(function successCallback(response) {
        console.log(response);
         $state.go('home');
      }, function errorCallback(error) {
        //$state.go('etiProd');
        console.log(error);
      });
    };

})

.controller('prodCtrlDet', function($scope, $http, $state, $stateParams) {
  $scope.token = localStorage.getItem("S-Token");
  $http({
       url: 'http://aangehrn.eemi.tech/webservice/index.php/api/product/listing',
       method: "POST",
      data: $stateParams,
       headers : {
         "S-Token" : $scope.token
       }
   }).then(function successCallback(response) {
     console.log(response.data);

     $scope.product = response.data
  }, function errorCallback(error) {
    //$state.go('etiProd');
    console.log(error);
  });
})


.controller('vinDetCtrl', function($scope, $http, $state, $stateParams) {
  $scope.token = localStorage.getItem("S-Token");

  $http({
       url: 'http://aangehrn.eemi.tech/webservice/index.php/api/vin/detail',
       method: "POST",
      data: $stateParams,
       headers : {
         "S-Token" : $scope.token
       }
   }).then(function successCallback(response) {
     console.log(response.data);

     $scope.product = response.data
  }, function errorCallback(error) {
    //$state.go('etiProd');
    console.log(error);
  });
})

.controller('vinCtrl', function($scope, $http, $state, $stateParams) {
  $scope.vinData = {};
  $scope.commandData = {};
    $scope.commandData.quantite = 1
    $scope.vinData.quantite = 1
  

  $scope.token = localStorage.getItem("S-Token");

  $http({
       url: 'http://aangehrn.eemi.tech/webservice/index.php/api/vinCat/list',
       method: "GET",
       headers : {
         "S-Token" : $scope.token
       }
   }).then(function successCallback(response) {
         console.log(response.data);

     $scope.cats = response.data

  }, function errorCallback(error) {
    //$state.go('etiProd');
    console.log(error);
  });

     $http({
       url: 'http://aangehrn.eemi.tech/webservice/index.php/api/vin/list',
       method: "GET",
       headers : {
         "S-Token" : $scope.token
       }
   }).then(function successCallback(response) {
         console.log(response.data);

     $scope.products = response.data

  }, function errorCallback(error) {
    //$state.go('etiProd');
    console.log(error);
  });

  $http({
       url: 'http://aangehrn.eemi.tech/webservice/index.php/api/productFour/list',
       method: "GET",
       headers : {
         "S-Token" : $scope.token
       }
   }).then(function successCallback(response) {
     $scope.fourns = response.data

  }, function errorCallback(error) {
    //$state.go('etiProd');
    console.log(error);
  });

$scope.plus = function() {
  $scope.vinData.quantite = $scope.vinData.quantite+1
}
$scope.minus = function() {
  $scope.vinData.quantite = $scope.vinData.quantite-1
}

$scope.plusc = function() {
  $scope.commandData.quantite = $scope.commandData.quantite+1
}
$scope.minusc = function() {
  $scope.commandData.quantite = $scope.commandData.quantite-1
}

$scope.det = function(id, $location) {
  $location.path('#/stockProdDet/'+id);
}
  $scope.enter = function() {
    $http({
         url: 'http://aangehrn.eemi.tech/webservice/index.php/api/product/new_vin',
         method: "POST",
         data: $scope.vinData,
         headers : {
           "S-Token" : $scope.token
         }
     }).then(function successCallback(response) {
       $state.go('etivProd');
    }, function errorCallback(error) {
      //$state.go('etiProd');
      $state.go('home');
    });
  };




    $scope.order = function() {
      $http({
           url: 'http://aangehrn.eemi.tech/webservice/index.php/api/vin/new_command',
           method: "POST",
           data: $scope.commandData,
           headers : {
             "S-Token" : $scope.token
           }
       }).then(function successCallback(response) {
          console.log(response);
         $state.go('home');
      }, function errorCallback(error) {
        //$state.go('etiProd');
        console.log(error);
      });
    };

})


.controller('etiCtrl', function($scope, $http, $state) {
  $scope.token = localStorage.getItem("S-Token");

  $http({
       url: 'http://aangehrn.eemi.tech/webservice/index.php/api/product/last_product',
       method: "GET",
       headers : {
         "S-Token" : $scope.token
       }
   }).then(function successCallback(response) {

     $scope.product = response.data[0]
  }, function errorCallback(error) {
    //$state.go('etiProd');
    console.log(error);
  });

  $scope.print = function(printSectionId) {
      var innerContents = document.getElementById(printSectionId).innerHTML;
      var popupWinindow = window.open('', '_blank', 'width=600,height=700,scrollbars=no,menubar=no,toolbar=no,location=no,status=no,titlebar=no');
      popupWinindow.document.open();
      popupWinindow.document.write('<html><head><link rel="stylesheet" type="text/css" href="style.css" /></head><body onload="window.print()">' + innerContents + '</html>');
      popupWinindow.document.close();
  };

  $scope.home = function() {
    $state.go('home');
  };
})



.controller('etivCtrl', function($scope, $http, $state) {
  $scope.token = localStorage.getItem("S-Token");

  $http({
       url: 'http://aangehrn.eemi.tech/webservice/index.php/api/vin/last',
       method: "GET",
       headers : {
         "S-Token" : $scope.token
       }
   }).then(function successCallback(response) {
console.log(response);
     $scope.product = response.data[0]
  }, function errorCallback(error) {
    //$state.go('etiProd');
    console.log(error);
  });

  $scope.print = function(printSectionId) {
      var innerContents = document.getElementById(printSectionId).innerHTML;
      var popupWinindow = window.open('', '_blank', 'width=600,height=700,scrollbars=no,menubar=no,toolbar=no,location=no,status=no,titlebar=no');
      popupWinindow.document.open();
      popupWinindow.document.write('<html><head><link rel="stylesheet" type="text/css" href="style.css" /></head><body onload="window.print()">' + innerContents + '</html>');
      popupWinindow.document.close();
  };

  $scope.home = function() {
    $state.go('home');
  };
})

.controller('reciCtrl', function($scope, $http, $state, $stateParams) {
  $scope.recipeData = {};
  $scope.token = localStorage.getItem("S-Token");


  $scope.addReci = function() {
    $state.go('addReci');
  };
  $scope.listReci = function() {
    $state.go('listReci');
  };

  $scope.add = function() {
    $http({
         url: 'http://aangehrn.eemi.tech/webservice/index.php/api/recette/new_recette',
         method: "POST",
         data: $scope.recipeData,
         headers : {
           "S-Token" : $scope.token
         }
     }).then(function successCallback(response) {
       $state.go('home');
    }, function errorCallback(error) {
      //$state.go('etiProd');
      console.log(error);
    });
  };

    $http({
       url: 'http://aangehrn.eemi.tech/webservice/index.php/api/productCat/list',
       method: "GET",
       headers : {
         "S-Token" : $scope.token
       }
   }).then(function successCallback(response) {
     console.log(response.data);

     $scope.products = response.data
  }, function errorCallback(error) {
    //$state.go('etiProd');
    console.log(error);
  });

  $http({
       url: 'http://aangehrn.eemi.tech/webservice/index.php/api/recette/list_recette',
       method: "GET",
       headers : {
         "S-Token" : $scope.token
       }
   }).then(function successCallback(response) {
     $scope.recettes = response.data
  }, function errorCallback(error) {
    //$state.go('etiProd');
    console.log(error);
  });

})

.controller('ventesCtrl', function($scope, $http, $state, $stateParams) {
  $scope.venteData = {};
  $scope.par = "Ventes par jour"

  $scope.token = localStorage.getItem("S-Token");



  $scope.add = function() {
    $http({
         url: 'http://aangehrn.eemi.tech/webservice/index.php/api/vente/new_vente/',
         method: "POST",
         data: $scope.venteData,
         headers : {
           "S-Token" : $scope.token
         }
     }).then(function successCallback(response) {
       $state.go('ventes');
    }, function errorCallback(error) {
      //$state.go('etiProd');
      console.log(error);
    });
  };

  $scope.day = function() {
    $scope.par = "Ventes par jour"

    $http({
      url: 'http://aangehrn.eemi.tech/webservice/index.php/api/vente/list_ventes/day',
         method: "GET",
         headers : {
           "S-Token" : $scope.token
         }
     }).then(function successCallback(response) {
       $scope.ventes = response.data
    }, function errorCallback(error) {
      //$state.go('etiProd');
      console.log(error);
    });
  };

  $scope.week = function() {
    $scope.par = "Ventes par semaine"

    $http({
      url: 'http://aangehrn.eemi.tech/webservice/index.php/api/vente/list_ventes/week',
         method: "GET",
         headers : {
           "S-Token" : $scope.token
         }
     }).then(function successCallback(response) {
       $scope.ventes = response.data
    }, function errorCallback(error) {
      //$state.go('etiProd');
      console.log(error);
    });
  };

  $scope.month = function() {
    $scope.par = "Ventes par mois"

    $http({
      url: 'http://aangehrn.eemi.tech/webservice/index.php/api/vente/list_ventes/month',
         method: "GET",
         headers : {
           "S-Token" : $scope.token
         }
     }).then(function successCallback(response) {
       $scope.ventes = response.data
    }, function errorCallback(error) {
      //$state.go('etiProd');
      console.log(error);
    });
  };


$scope.year = function() {
  $scope.par = "Ventes par année"

      $http({
        url: 'http://aangehrn.eemi.tech/webservice/index.php/api/vente/list_ventes/day',
           method: "GET",
           headers : {
             "S-Token" : $scope.token
           }
       }).then(function successCallback(response) {
         $scope.ventes = response.data
      }, function errorCallback(error) {
        //$state.go('etiProd');
        console.log(error);
      });
    };

  $http({
       url: 'http://aangehrn.eemi.tech/webservice/index.php/api/vente/list_ventes/day',
       method: "GET",
       headers : {
         "S-Token" : $scope.token
       }
   }).then(function successCallback(response) {
        console.log(response);

     $scope.ventes = response.data
  }, function errorCallback(error) {
    //$state.go('etiProd');
    console.log(error);
  });

  $http({
       url: 'http://aangehrn.eemi.tech/webservice/index.php/api/recette/list_recette',
       method: "GET",
       headers : {
         "S-Token" : $scope.token
       }
   }).then(function successCallback(response) {
    console.log(response);
     $scope.recettes = response.data
  }, function errorCallback(error) {
    //$state.go('etiProd');
    console.log(error);
  });

})

.controller('statsCtrl', function($scope, $http, $state, $stateParams) {
  $scope.venteData = {};
  $scope.par = "Ventes par jour"

  $scope.token = localStorage.getItem("S-Token");

var data = google.visualization.arrayToDataTable([
        ['titre', 'valeur'],
          ['Recettes',     1230],
          ['Dépenses',      952]
      ]);
      var options = {
        title: 'Activités',
                  pieHole: 0.6,
                            colors: ['#1D6885', '#159B83']


      };
      var chart = new google.visualization.PieChart(document.getElementById('donutchart'));

      chart.draw(data, options);




  $scope.day = function() {
    $scope.par = "Ventes par jour"
var data = google.visualization.arrayToDataTable([
        ['titre', 'valeur'],
          ['Recettes',     1230],
          ['Dépenses',      952]
      ]);
      var options = {
        title: 'Activités',
                  pieHole: 0.6,
                            colors: ['#1D6885', '#159B83']


      };
      var chart = new google.visualization.PieChart(document.getElementById('donutchart'));

      chart.draw(data, options);
  };

  $scope.week = function() {
      $scope.par = "Ventes par semaine"

  var data = google.visualization.arrayToDataTable([
          ['titre', 'valeur'],
          ['Recettes',     9624],
          ['Dépenses',      6533]
        ]);

  var options = {
        title: 'Activités',
                  pieHole: 0.6,
                            colors: ['#1D6885', '#159B83']


      };
      var chart = new google.visualization.PieChart(document.getElementById('donutchart'));

      chart.draw(data, options);
  };

  $scope.month = function() {
      $scope.par = "Ventes par mois"

   var data = google.visualization.arrayToDataTable([
              ['titre', 'valeur'],
          ['Recettes',     335682],
          ['Dépenses',      176655]

        ]);

  var options = {
        title: 'Activités',
                  pieHole: 0.6,
                            colors: ['#1D6885', '#159B83']


      };
      var chart = new google.visualization.PieChart(document.getElementById('donutchart'));

      chart.draw(data, options);
  };


$scope.year = function() {
    $scope.par = "Ventes par année"

   var data = google.visualization.arrayToDataTable([
           ['titre', 'valeur'],
          ['Recettes',     29894],
          ['Dépenses',      17665]
        ]);
   var options = {
        title: 'Activités',
                  pieHole: 0.6,
                            colors: ['#1D6885', '#159B83']


      };
      var chart = new google.visualization.PieChart(document.getElementById('donutchart'));

      chart.draw(data, options);
    };

  $http({
       url: 'http://aangehrn.eemi.tech/webservice/index.php/api/vente/list_ventes/day',
       method: "GET",
       headers : {
         "S-Token" : $scope.token
       }
   }).then(function successCallback(response) {
console.log(response);
     $scope.ventes = response.data
  }, function errorCallback(error) {
    //$state.go('etiProd');
    console.log(error);
  });

  $http({
       url: 'http://aangehrn.eemi.tech/webservice/index.php/api/recette/list_recette/',
       method: "GET",
       headers : {
         "S-Token" : $scope.token
       }
   }).then(function successCallback(response) {
     $scope.recettes = response.data
  }, function errorCallback(error) {
    //$state.go('etiProd');
    console.log(error);
  });

})

.controller('PlaylistCtrl', function($scope, $stateParams) {
});
