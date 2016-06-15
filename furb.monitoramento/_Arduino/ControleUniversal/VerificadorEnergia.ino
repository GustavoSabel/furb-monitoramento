//Retorna 1 se tiver corrente
int verificarCorrente() {
  int saida = 0;
  
  //Calcula a corrente  
  double irms = energyMonitor.calcIrms(3000);
  
  if(irms > LIMITE_CORRENTE) {
    saida = 1;
  }
  
  /*Serial.print(" * Corrente : ");
  Serial.print(irms); // Irms
   
  //Calcula e mostra o valor da potencia
  Serial.print(" Potencia : ");
  Serial.println(irms * REDE);*/
  
  return saida;
}
