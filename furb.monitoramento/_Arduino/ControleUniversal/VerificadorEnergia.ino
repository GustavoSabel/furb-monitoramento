//Retorna 1 se tiver corrente maior que 1 ampere
int temCorrente() {
  int saida = 0;
  //Calcula a corrente
  double Irms = 0;
  //Calcula algumas vezes antes para descarregar o capacitor
  int x = 10;
  Serial.println("Descarregando capacitor");
  while(x-- > 0) {
    Irms = energyMonitor.calcIrms(1480);
    Serial.print(Irms);
    Serial.print(" ");
    delay(50);
  }
  Serial.println(" descarregado");
  Irms = energyMonitor.calcIrms(1480);
  delay(10);
  if(Irms > LIMITE_CORRENTE) {
    Serial.print("TEM CORRENTE:");
    saida = 1;
  } else {
    Serial.print("NAO TEM CORRENTE:");
  }
  delay(10);
  Serial.println(Irms);
  delay(10);
  return saida;
}
