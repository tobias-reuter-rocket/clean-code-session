class Human {
  // ...
}

const Fighter = Sup => class extends Sup {
  hit() {
    console.log('Hit with fist!');
  }
};

const Armed = Sup => class extends Sup {
  hitWithTaser() {
    console.log('Tzzzzt!');
  }
};

class Hero extends Armed(Fighter(Human)) {
  constructor (name) {
    super(name);
  }
  greet() {
    console.log('So, you wanna play?');
  }
}

const kickAss = new Hero('das');

kickAss.greet();
kickAss.hit();
kickAss.hitWithTaser();

// So, you wanna play?
// Hit with fist!
// Tzzzzt!
