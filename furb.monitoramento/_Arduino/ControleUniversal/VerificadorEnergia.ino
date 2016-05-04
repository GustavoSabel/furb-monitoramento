//Retorna 1 se tiver corrente maior que 1 ampere
int verificarCorrente() {
  int saida = 0;
  double Irms = 0;
  Irms = energyMonitor.calcIrms(1480);
  if(Irms > LIMITE_CORRENTE) {
    saida = 1;
  }
  Serial.println(Irms);
  return saida;
}
