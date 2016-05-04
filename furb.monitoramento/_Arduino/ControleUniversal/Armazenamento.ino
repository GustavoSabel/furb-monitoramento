
void gravarNoEEPRON(Comando *command){
  Serial.println("Salvando comandos na memoria");   
  int addr = command->id * ESPACO_RESERVADO_EEPROM;
  EEPROM.put(addr, CODE_EEPROM);
  addr += sizeof(long);
  EEPROM.put(addr, command->id);
  addr += sizeof(int);
  EEPROM.put(addr, command->codeType);
  addr += sizeof(int);
  EEPROM.put(addr, command->codeLen);
  addr += sizeof(int);
  EEPROM.put(addr, command->verificarCorrente);
  addr += sizeof(int);
  if(command->codeType != UNKNOWN) {
    EEPROM.put(addr, command->codeValue);
    addr += sizeof(long);
    EEPROM.put(addr, command->toggle);
    addr += sizeof(char);
  } else {
    EEPROM.put(addr, command->rawCodes);
    addr += sizeof(int) * RAWBUF;
  }
}

//Busca o comando salvo na memória
void carregarComandoEEPRON(int comandoId, Comando *command) {
  Serial.println("Buscando comando da memoria");

  int addr = comandoId * ESPACO_RESERVADO_EEPROM;
  //Pula o código de verificação
  addr += sizeof(long);

  Serial.print(addr);
  Serial.print(" - id: ");
  Serial.println(EEPROM.get(addr, command->id));
  addr += sizeof(int);

  Serial.print(addr);
  Serial.print(" - codeType: ");
  Serial.println(EEPROM.get(addr, command->codeType));
  addr += sizeof(int);
  
  Serial.print(addr);
  Serial.print(" - codeLen: ");
  Serial.println(EEPROM.get(addr, command->codeLen));
  addr += sizeof(int);

  Serial.print(addr);
  Serial.print(" - verificarCorrente: ");
  Serial.println(EEPROM.get(addr, command->verificarCorrente));
  addr += sizeof(int);

  if(command->codeType != UNKNOWN) {
    Serial.print(addr);
    Serial.print(" - codeValue: ");
    Serial.println(EEPROM.get(addr, command->codeValue));
    addr += sizeof(long);
    
    Serial.print(addr);
    Serial.print(" - toggle: ");
    Serial.println(EEPROM.get(addr, command->toggle));
    addr += sizeof(char);
  } else {
    Serial.print(addr);
    Serial.print(" - rawCode: ");
    EEPROM.get(addr, command->rawCodes);
    addr += sizeof(int) * RAWBUF;
    printCodigoBruto(command);
  }
  Serial.print(addr);
  Serial.println(" - fim");
}

//Retorna 1 se o comando está salvo na memória EEPROM
//Retorna 0 caso não esteja
int comandoEstaSalvo(int comandoId) {
  long code_eepron = 0;
  int addr = comandoId * ESPACO_RESERVADO_EEPROM;
  Serial.println(EEPROM.get(addr, code_eepron));
  addr += sizeof(long);
  if(code_eepron != CODE_EEPROM) {
    Serial.println("O comando ");
    Serial.print(comandoId);
    Serial.println(" não está salvo na memória");
    return 0;
  } else {
    return 1;
  }
}
