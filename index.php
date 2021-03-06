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
        
        
        
        <div id="page">
            <div id="inner-wrapper">
               <header id="header">
                        <h2>WEB SCADA</h2>
                </header>



                <div id="content">
                                
                    <div id="scrubber">
                    <img src="images/scrubber1.png" alt="scrubber" />
                    </div>
                        
                    
                               
                    <div id="right-part">
                            <div id="form">
                            <form>
                                 <div class="left">
                                <p> Температура у скрубері:
                                    <output name="Значення"><b id="temp"></b> <b><sup>o</sup>C</b></output> 
                                </p>
                                <p> Витрата NaOH:
                                 <output name="temp"><b id="naohinp"></b> <b>кг/секундку</b></output>
                                </p>
                                
                                <p> Концентрація CO<sub>2</sub> на виході:
                                  <output name="Значення"><b id="co2out"></b> <b>%</b></output>
                                  <input name="Значення" placeholder="Цільове значення"/>
                                </p>
                                </div>
                                
                                <div class="right">
                                <p> Концентрація CO<sub>2</sub> на вході:
                                    <output name="Значення"><b id="co2inp"></b> <b>%</b></output>
                                </p>
                                <p> Витрата суміші C<sub>2</sub>H<sub>2</sub> та CO<sub>2</sub>:
                                 <output name="Значення"><b id="massinp"></b> <b>кг/секундку</b></output>
                                </p>
                                <p> Витрата шламу:
                                    <output name="Значення"><b id="shlam"></b> <b>кг/секундку</b></output>
                                </p>
                                </div>
                                 
                                
                            </form>
                                
                                <div class="stripe">
                                  <em>Ступінь відкриття клапана NaOH</em>
                                                                    
                                <div class="progress">
                                    
                                <div  id="pb1" class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 80%;">
                                   60%
                                </div>
                              </div>
                                  
                                 <em>Ступінь відкриття клапана C<sub>2</sub>H<sub>2</sub> та CO<sub>2</sub></em>
                                <div class="progress">
 
                                <div  id="pb2" class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 80%;">
                                   60%
                                </div>
                              </div>
                                 
                                 <em>Ступінь відкриття клапана шлам</em>
                                <div class="progress">
                                <div  id="pb3" class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 80%;">
                                   60%
                                </div>
                              </div>
                            
                              </div>
                                  
                                  <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                      Вибір графіка
                                      <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                      <li><a href="#">Графік зміни температури у скрубері</a></li>
                                      <li><a href="#">Графік зміни витрати NaOH</a></li>
                                       <li><a href="#">Графік зміни концентрації CO<sub>2</sub> на виході</a></li>
                                        <li><a href="#">Графік зміни витрати суміші C<sub>2</sub>H<sub>2</sub> та CO<sub>2</a></li>
                                         <li><a href="#">Графік температури з Arduino</a></li>
                                    </ul>
                                  </div>
                       
                            
                            </div>
                        
                    </div>
                    
                                    
                    
                                        
                    
                    <div id="chartContainer" style="height: 300px; width: 95%;"></div>
                    
                </div>

                
            </div>
        </div>
       
        <script type="text/javascript" src="js/chart-mock.js"></script>
    </body>
</html>