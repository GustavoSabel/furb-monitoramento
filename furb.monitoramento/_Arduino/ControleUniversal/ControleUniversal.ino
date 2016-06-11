#include "EmonLib.h"
#include <IRremote.h>
#include <EEPROM.h>

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
char BUTTON_1 = 8; //Botão que envia ou grava o sinal 1
char BUTTON_2 = 9; //Botão que envia ou grava o sinal 2
char PIN_VERIFICADOR = 12; //Se esse pino estiver HIGT, então deve-se verificar a corrente antes de mandar o comando de desligar
char RECV_PIN = 5; //Pino que irá ouvir os sinais IR
char BUTTON_LEARN = 10; //Se precionado, indica que o dispositivo está no modo aprendizagem
char STATUS_PIN = 7; //Pino para um LED exibir um status

char PIN_ESP8266 = 4; //Quando esse pino for maior que 500, então deve enviar os comandos de desligar

IRrecv irrecv(RECV_PIN);
IRsend irsend;
decode_results results;

int lastButton1State;
int lastButton2State;
int lastButtonLearnState;
int lastButtonEsp8266State;
int temCorrente;

struct Comando {
  int id;
  // Storage for the recorded code
  int codeType = -1; // The type of code
  unsigned long codeValue; // The code value if not raw
  unsigned int rawCodes[RAWBUF]; // The durations if raw
  int codeLen; // The length of the code
  char toggle = 0; // The RC5/6 toggle state
  int verificarCorrente = 0; //Se tiver com 1, deve-se verificar se existe corrente antes de enviar o sinal
} comando;

void setup() {
  Serial.begin(9600);
  irrecv.enableIRIn(); // Start the receiver
  pinMode(BUTTON_1, INPUT);
  pinMode(BUTTON_2, INPUT);
  pinMode(BUTTON_LEARN, INPUT);
  pinMode(STATUS_PIN, OUTPUT);
  pinMode(PIN_ESP8266, INPUT);

  //pinMode(PIN_SENSOR_CORRENTE, INPUT);
  pinMode(PIN_STATUS_SENSOR_CORRENTE, OUTPUT); 
  //Pino, calibracao - Cur Const= Ratio/BurdenR. 1800/62 = 29. 
  energyMonitor.current(PIN_SENSOR_CORRENTE, 29);

  Serial.println("SISTEMA INICIADO");
}

void loop() {
  int button1State = digitalRead(BUTTON_1);
  int button2State = digitalRead(BUTTON_2);
  int buttonLearnState = digitalRead(BUTTON_LEARN);
  int buttonEsp8266State = digitalRead(PIN_ESP8266);
  temCorrente = verificarCorrente();
  
  if(temCorrente) {
    digitalWrite(PIN_STATUS_SENSOR_CORRENTE, HIGH);
  } else {
    digitalWrite(PIN_STATUS_SENSOR_CORRENTE, LOW);
  }

  if(buttonLearnState == LOW){
    if (lastButtonLearnState == HIGH) {
      Serial.println("Busca por sinal IR finalizada");
    }
    
    if(lastButtonEsp8266State == LOW && buttonEsp8266State == HIGH) {
      Serial.println("Recebi comando do ESP8266");
      validarEnviarComando(COMANDO_1);
      validarEnviarComando(COMANDO_2);
    } else if (lastButton1State == LOW && button1State == HIGH) {
      validarEnviarComando(COMANDO_1);
    } else if (lastButton2State == LOW && button2State == HIGH) {
      validarEnviarComando(COMANDO_2);
    } 
  } else {
    if (lastButtonLearnState == LOW) {
      Serial.println("Aguardando sinal IR");
      irrecv.enableIRIn(); // Re-enable receiver
    }
    if (irrecv.decode(&results)) {
      if(button1State == HIGH){
        digitalWrite(STATUS_PIN, HIGH);
        //struct Comando comando;
        criarComando(&results, COMANDO_1, &comando);
        gravarNoEEPRON(&comando);
        digitalWrite(STATUS_PIN, LOW);
      }
      if(button2State == HIGH){
        digitalWrite(STATUS_PIN, HIGH);
        //struct Comando comando;
        criarComando(&results, COMANDO_2, &comando);
        gravarNoEEPRON(&comando);
        digitalWrite(STATUS_PIN, LOW);
      }
      irrecv.resume(); // resume receiver
    }
  }
  lastButton1State = button1State;
  lastButton2State = button2State;
  lastButtonLearnState = buttonLearnState;
  lastButtonEsp8266State = buttonEsp8266State;
}

//Convert *results em um comando
void criarComando(decode_results *results, int comandoId, Comando * comando) {
  Serial.print("Salvando o comando ");
  Serial.println(comandoId);
  comando->id = comandoId;
  comando->verificarCorrente = digitalRead(PIN_VERIFICADOR) == HIGH;
  comando->codeType = results->decode_type;
  int count = results->rawlen;
  int isRawCode = 0;
  
  if (comando->codeType == UNKNOWN) {
    Serial.println("Received unknown code, saving as raw");
    isRawCode = 1;
  } else if (comando->codeType == NEC) {
    Serial.print("Received NEC: ");
    if (results->value == REPEAT) {
      // Don't record a NEC repeat value as that's useless.
      Serial.println("repeat; ignoring.");
      return;
    }
    Serial.println(results->value, HEX);
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
    Serial.println(results->value, HEX);
    comando->codeValue = results->value;
    comando->codeLen = results->bits;
  } else {
    comando->codeValue = -1;
    comando->codeLen = results->rawlen - 1;
    
    // Para armazenar os dados brutos:
    // Descartar o primeiro valor;
    // converter para microsegundos;
    // Deixa as marcas menores e o espaços maiores para diminuir a distorção do receptor
    for (int i = 1; i <= comando->codeLen; i++) {
      if (i % 2) {
        // Mark
        comando->rawCodes[i - 1] = results->rawbuf[i]*USECPERTICK - MARK_EXCESS;
      } 
      else {
        // Space
        comando->rawCodes[i - 1] = results->rawbuf[i]*USECPERTICK + MARK_EXCESS;
      }
    }
    printCodigoBruto(comando);
  }
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

//Verifica se o comando está salvo em memória
//Verifica se o comando só deve ser enviado se ter corrente elétrica
//Se sim, verifica se tem corrente. Se ter corrente, envia o comando
//Se não precisa verificar a existencia de corrente, apenas envia o comando
//O comando será enviado duas vezes, com um pequeno tempo entre os envios
void validarEnviarComando(int comandoId) {
  Serial.print("Preparando para enviar o comando ");
  Serial.println(comandoId);
  //Serial.println();
  //Se esse pino estiver em alto, então deve-se verificar se existe corrente antes de mandar o comando
  //TODO: Armazenar no comando se o mesmo deve verificar a corrente ou não
  if(comandoEstaSalvo(comandoId)) {
    //struct Comando comando;
    carregarComandoEEPRON(comandoId, &comando);
    if(!comando.verificarCorrente || temCorrente) {
      if(comando.verificarCorrente)
        Serial.println("Tem corrente para enviar o comando");
      digitalWrite(STATUS_PIN, HIGH);
      enviarCodigo(0, &comando);
      delay(50);
      enviarCodigo(REPEAT, &comando);
      digitalWrite(STATUS_PIN, LOW);
      //Aguarda alguns segundos para depois enviar o comando novamente
      //Isso é necessário caso o aparelho que se deseja desligar seja um projetor.
      //Para desligar o projeto é preciso enviar o comando de desligar duas vezes.
      delay(2000); 
      digitalWrite(STATUS_PIN, HIGH);
      enviarCodigo(0, &comando);
      delay(50);
      enviarCodigo(REPEAT, &comando);
      digitalWrite(STATUS_PIN, LOW);
      delay(50);
    } else {
      Serial.println("Sem corrente para enviar o sinal");
    }
  } else {
    Serial.print("Nenhum comando salvo para o botao ");
    Serial.println(comandoId);
  }
}

void enviarCodigo(int repeat, Comando *comando) {  
  Serial.print("Enviando o comando ");
  Serial.print(comando->id);
  Serial.print(": ");

#if (DECODE_RC5 || DECODE_RC6)
  if (comando->codeType == RC5 || comando->codeType == RC6) {
    if (!repeat) {
      // Flip the toggle bit for a new button press
      comando->toggle = 1 - comando->toggle;
    }
    // Put the toggle bit into the code to send
    comando->codeValue = comando->codeValue & ~(1 << (comando->codeLen - 1));
    comando->codeValue = comando->codeValue | (comando->toggle << (comando->codeLen - 1));
    if (comando->codeType == RC5) {
      Serial.print("Sent RC5 ");
      Serial.println(comando->codeValue, HEX);
      irsend.sendRC5(comando->codeValue, comando->codeLen);
    } else {
      irsend.sendRC6(comando->codeValue, comando->codeLen);
      Serial.print("Sent RC6 ");
      Serial.println(comando->codeValue, HEX);
    }
    return;
  }
#endif

//Projeto da marca EPSON utiliza esse tipo de código
#if DECODE_NEC
  if (comando->codeType == NEC) {
    if (repeat) {
      irsend.sendNEC(REPEAT, comando->codeLen);
      Serial.println("Sent NEC repeat");
    } else {
      irsend.sendNEC(comando->codeValue, comando->codeLen);
      Serial.print("Sent NEC ");
      Serial.println(comando->codeValue, HEX);
    }
    return;
  }
#endif

#if DECODE_SONY
  if (comando->codeType == SONY) {
    irsend.sendSony(comando->codeValue, comando->codeLen);
    Serial.print("Sent Sony ");
    Serial.println(comando->codeValue, HEX);
    return;
  } 
#endif

#if DECODE_PANASONIC
  if (comando->codeType == PANASONIC) {
    irsend.sendPanasonic(comando->codeValue, comando->codeLen);
    Serial.print("Sent Panasonic");
    Serial.println(comando->codeValue, HEX);
    return;
  }
#endif

#if DECODE_JVC
  if (comando->codeType == JVC) {
    irsend.sendPanasonic(comando->codeValue, comando->codeLen);
    Serial.print("Sent JVC");
    Serial.println(comando->codeValue, HEX);
    return;
  }
#endif

  //Enviar comando bruto
  //Assume 38 KHz
  irsend.sendRaw(comando->rawCodes, comando->codeLen, 38);
  Serial.println("Emitindo codigo IR bruto");    
  printCodigoBruto(comando);
}

