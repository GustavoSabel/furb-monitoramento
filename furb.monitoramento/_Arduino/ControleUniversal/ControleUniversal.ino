#include "EmonLib.h"
#include "IRremote.h"
#include "EEPROM.h"

//Variáveis para detecção de corrente
EnergyMonitor energyMonitor;
int REDE = 220; //Tensao da rede eletrica
int PIN_SENSOR_CORRENTE = A1; //Pino do sensor de corrente
int PIN_STATUS_SENSOR_CORRENTE = 13;

//Se ultrapassar esse valor, significa que tem corrente. 
//Mes com o aparelho desligado, o sistema apresenta uma corrente entre 0.03A e 0.06A
double LIMITE_CORRENTE = 0.15; 

int ESPACO_RESERVADO_EEPROM = 500; //Espaço que cada comando ocupará
int COMANDO_1 = 0; //Posição da memória em que ficará salvo
int COMANDO_2 = 1; //Posição da memória em que ficará salvo
long CODE_EEPROM = 112358; //Se esse número estiver na primeira posição, então tem algo salvo lá

//Deve ser conectado um LED IR no pino 3
char PIN_BOTAO_1 = 8; //Botão que envia ou grava o sinal 1
char PIN_BOTAO_2 = 9; //Botão que envia ou grava o sinal 2
char PIN_VERIFICADOR = 12; //Se esse pino estiver HIGT, então deve-se verificar a corrente antes de mandar o comando de desligar
char PIN_IR_RECEPTOR = 5; //Pino que irá ouvir os sinais IR
char PIN_BOTAO_APRENDER = 10; //Se precionado, indica que o dispositivo está no modo aprendizagem
char PIN_IR_STATUS = 7; //Pino para um LED exibir um status

char PIN_ESP8266 = 4; //Quando estiver em HIGH, então os comandos IR devem ser enviados

IRrecv irReceptor(PIN_IR_RECEPTOR);
IRsend irEmissor;
decode_results resultadoIR;

int ultimoBotao1Estado;
int ultimoBotao2Estado;
int ultimoBotaoAprenderEstado;
int ultimaEntradaEsp8266Estado;
int temCorrente;

struct Comando {
  int id;
  int codeType = -1; // O tipo do código
  unsigned long codeValue; // O código quando não utilizado o códido bruto
  unsigned int rawCodes[RAWBUF]; // As durações do código quando for bruto
  int codeLen; // O tamanho do código
  char toggle = 0; // The RC5/6 toggle state
  int verificarCorrente = 0; //Se tiver com 1, deve-se verificar se existe corrente antes de enviar o sinal
} comando;

void setup() {
  Serial.begin(9600);
  pinMode(PIN_BOTAO_1, INPUT);
  pinMode(PIN_BOTAO_2, INPUT);
  pinMode(PIN_BOTAO_APRENDER, INPUT);
  pinMode(PIN_IR_STATUS, OUTPUT);
  pinMode(PIN_ESP8266, INPUT);

  pinMode(PIN_STATUS_SENSOR_CORRENTE, OUTPUT); 
  //Ratio/Burden Resistor. 
  int calibracao = 1800/62;
  energyMonitor.current(PIN_SENSOR_CORRENTE, calibracao);

  Serial.println("SISTEMA INICIADO");
}

void loop() {
  int botao1Estado = digitalRead(PIN_BOTAO_1);
  int botao2Estado = digitalRead(PIN_BOTAO_2);
  int botaoAprenderEstado = digitalRead(PIN_BOTAO_APRENDER);
  int entradaEsp8266Estado = digitalRead(PIN_ESP8266);

  if(botaoAprenderEstado == LOW){
    if (ultimoBotaoAprenderEstado == HIGH) {
      Serial.println("Busca por sinal IR finalizada");
    }
    temCorrente = verificarCorrente();
    if(ultimaEntradaEsp8266Estado == LOW && entradaEsp8266Estado == HIGH) {
      Serial.println("Comando recebido do ESP8266");
      validarEnviarComando(COMANDO_1);
      validarEnviarComando(COMANDO_2);
    } else if (ultimoBotao1Estado == LOW && botao1Estado == HIGH) {
      Serial.println("Botao 1 foi pressionado");
      validarEnviarComando(COMANDO_1);
    } else if (ultimoBotao2Estado == LOW && botao2Estado == HIGH) {
      Serial.println("Botao 2 foi pressionado");
      validarEnviarComando(COMANDO_2);
    } 
  } else {
    if (ultimoBotaoAprenderEstado == LOW) {
      Serial.println("Aguardando sinal IR");
      irReceptor.enableIRIn(); // Ativa o receptor IR
    }
    if (irReceptor.decode(&resultadoIR)) {
      if(botao1Estado == HIGH){
        criarComando(&resultadoIR, COMANDO_1, &comando);
      }
      if(botao2Estado == HIGH){
        criarComando(&resultadoIR, COMANDO_2, &comando);
      }
      irReceptor.resume(); // reinicia receptor
    }
  }
  ultimoBotao1Estado = botao1Estado;
  ultimoBotao2Estado = botao2Estado;
  ultimoBotaoAprenderEstado = botaoAprenderEstado;
  ultimaEntradaEsp8266Estado = entradaEsp8266Estado;
}

//Convert *resultadoIR em um comando
void criarComando(decode_results *resultadoIR, int comandoId, Comando * comando) {
  digitalWrite(PIN_IR_STATUS, HIGH);
  Serial.print("Salvando o comando ");
  Serial.println(comandoId);
  comando->id = comandoId;
  comando->verificarCorrente = digitalRead(PIN_VERIFICADOR) == HIGH;
  comando->codeType = resultadoIR->decode_type;
  int count = resultadoIR->rawlen;
  int isRawCode = 0;
  
  if (comando->codeType == UNKNOWN) {
    Serial.println("Received unknown code, saving as raw");
    isRawCode = 1;
  } else if (comando->codeType == NEC) {
    Serial.print("Received NEC: ");
    if (resultadoIR->value == REPEAT) {
      // Don't record a NEC repeat value as that's useless.
      Serial.println("repeat; ignoring.");
      digitalWrite(PIN_IR_STATUS, LOW);
      return;
    }
    Serial.println(resultadoIR->value, HEX);
  } else if (comando->codeType == SONY) {
    Serial.print("Received SONY: ");
  } else if (comando->codeType == PANASONIC) {
    Serial.print("Received PANASONIC: ");
  } else if (comando->codeType == JVC) {
    Serial.print("Received JVC: ");
  } else if (comando->codeType == RC5) {
    Serial.print("Received RC5: ");
  } else if (comando->codeType == RC6) {
    Serial.print("Received RC6: ");
  } else {
    Serial.print("Unexpected codeType ");
    Serial.println(comando->codeType, DEC);
    comando->codeType = UNKNOWN;
    isRawCode = 1;
  }
  
  if(isRawCode == 0) {
    Serial.println(resultadoIR->value, HEX);
    comando->codeValue = resultadoIR->value;
    comando->codeLen = resultadoIR->bits;
  } else {
    comando->codeValue = -1;
    comando->codeLen = resultadoIR->rawlen - 1;
    
    // Para armazenar os dados brutos:
    // Descartar o primeiro valor;
    // converter para microsegundos;
    // Deixa as marcas menores e o espaços maiores para diminuir a distorção do receptor
    for (int i = 1; i <= comando->codeLen; i++) {
      if (i % 2) {
        // Mark
        comando->rawCodes[i - 1] = resultadoIR->rawbuf[i]*USECPERTICK - MARK_EXCESS;
      } 
      else {
        // Space
        comando->rawCodes[i - 1] = resultadoIR->rawbuf[i]*USECPERTICK + MARK_EXCESS;
      }
    }
    printCodigoBruto(comando);
  }
  gravarNoEEPROM(comando);
  digitalWrite(PIN_IR_STATUS, LOW);
}

void printCodigoBruto(Comando *command) {
  /*for (int i = 1; i <= command->codeLen; i++) {
    if (i % 2) {
      Serial.print(" m"); // Mark
    } 
    else {
      Serial.print(" s"); // Space
    }
    Serial.print(command->rawCodes[i - 1], DEC);
  }
  Serial.println("");*/
  Serial.print(command->codeLen);
  Serial.println(" bytes");
}

//Verifica se é possível enviar o comando e envia
void validarEnviarComando(int comandoId) {
  //Verifica se existe comando salvo na memória EEPRON
  if(comandoEstaSalvoEEPROM(comandoId)) {
    //Busca o comando salvo na memória
    carregarComandoEEPROM(comandoId, &comando);
    //Verifica se o comando exige a verificação de corrente. Se sim, verifica se tem corrente
    if(!comando.verificarCorrente) {
      enviarComando(&comando);
    } else {
      int numMaxTentativas = 6;
      while(temCorrente && numMaxTentativas-- > 0) {
        enviarComando(&comando);
        delay(2000);
        temCorrente = verificarCorrente();
      }
    }
  }
}

//Envia o comando
void enviarComando(Comando *comando) {  
  digitalWrite(PIN_IR_STATUS, HIGH);
  irEmissor.sendRaw(comando->rawCodes, comando->codeLen, 38);
  digitalWrite(PIN_IR_STATUS, LOW);
  delay(50);
}
