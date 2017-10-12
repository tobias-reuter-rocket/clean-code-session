
const martialArtist = (state) => ({
  hit: () => console.log('Fly kick!')
})
const vulgar = (state) => ({
  taunt: () => console.log('"Show\'s over, mo*******rs!"')
})

const BadassHero = (name)  => {
  const state = {
    name
  };
  return Object.assign(
    {},
    martialArtist(state),
    vulgar(state)
  )
}
const hitGirl = BadassHero('Hit-Girl');
hitGirl.taunt();
hitGirl.hit();
// "Show's over, mo*******rs!"
// Fly kick!
