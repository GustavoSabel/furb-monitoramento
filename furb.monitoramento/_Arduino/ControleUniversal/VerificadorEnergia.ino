//Retorna 1 se for identificado alguma corrente elétrica e 0 se não tiver
int verificarCorrente() {
  int saida = 0;
  
  //Calcula a corrente  
  double irms = energyMonitor.calcIrms(3000);
  
  if(irms > LIMITE_CORRENTE) {
    saida = 1;
  }
  
  /*Serial.print(" * Corrente : ");
  Serial.print(irms); // Irms
  Serial.print(" Potencia : ");
  Serial.println(irms * REDE);*/

  if(saida) {
    digitalWrite(PIN_STATUS_SENSOR_CORRENTE, HIGH);
  } else {
    digitalWrite(PIN_STATUS_SENSOR_CORRENTE, LOW);
  }
    
  return saida;
}
