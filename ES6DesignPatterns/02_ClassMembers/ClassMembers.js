
class Human {
  constructor(name) {
    this.name = name;
  }

  greet() {
    console.log(`Hi, I'm ${this.name}`);
  }
}

class Hero extends Human {
  constructor(name) {
    super(name);
  }

  greet() {
    super.greet();
    console.log('Why don\'t you pick on someone your own size?');
  }

  hit() {
    // ...
  }
}

const kickAss = new Hero('Kick-Ass');
kickAss.greet();
// Hi, I'm Kick-Ass
// Why don't you pick on someone your own size?
