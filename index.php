<!doctype html>
<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Web SCADA</title>
       <link href="css/style.css" rel="stylesheet" type="text/css" > 
          <link href="css/bootstrap.min.css" rel="stylesheet">
        <script type="text/javascript" src="js/jquery-1.11.1.min.js"></script>
        <script type="text/javascript" src="js/canvasjs.min.js"></script>
         <script src="js/bootstrap.min.js"></script>
        
    </head>
    <body>
        
        
        
        <div id="wrapper">
            <div id="inner-wrapper">
                <header id="header">
                    <img src="images/header.png" alt="header" />
                </header>



                <div id="content">
                                
                    <div id="scrubber">
                    <img src="images/scrubber1.png" alt="scrubber" />
                    </div>

                    <div id="right-part">
                        <img src="images/operator.png" alt="operator" />

                            <div id="form">
                            <form>
                                <p> Температура у скрубері:
                                    <output name="Значення"><b id="temp"></b> <b><sup>o</sup>C</b></output> 
                                </p>
                                <p> Витрата NaOH:
                                 <output name="temp"><b id="naohinp"></b> <b>кг/секундку</b></output>
                                </p>
                                <p> Концентрація CO<sub>2</sub> на вході:
                                    <output name="Значення"><b id="co2inp"></b> <b>%</b></output>
                                </p>
                                <p> Витрата суміші C<sub>2</sub>H<sub>2</sub> та CO<sub>2</sub>:
                                 <output name="Значення"><b id="massinp"></b> <b>кг/секундку</b></output>
                                </p>
                                <p> Витрата шламу:
                                    <output name="Значення"><b id="shlam"></b> <b>кг/секундку</b></output>
                                </p>
                                 <p> Концентрація CO<sub>2</sub> на виході:
                                  <output name="Значення"><b id="co2out"></b> <b>%</b></output>
                                  <input name="Значення" placeholder="Specify your value (integer)" value="2%" />
                                </p>
                                
                            </form>
                                
                                
                                  <em>Ступінь відкриття клапана</em>
                                <div class="progress">
                                <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%;">
                                   60%
                                </div>
                              </div>
                            
                              
                                  
                                  <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                      Вибір графіка
                                      <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                      <li><a href="#">Графік зміни температури у скрубері</a></li>
                                       <li><a href="#">Графік зміни концентрації CO<sub>2</sub> на виході</a></li>
                                         <li><a href="#">Графік температури з Arduino</a></li>
                                    </ul>
                                  </div>
                       
                            
                            </div>
                        
                    </div>
                    
                    <div id="chartContainer" style="height: 300px; width: 95%;"></div>
                    
                </div>

                <footer id="footer">Гуза Катерина - КПІ &copy; 2017</footer>
            </div>
        </div>
       
        <script type="text/javascript" src="js/chart-mock.js"></script>
    </body>
</html>