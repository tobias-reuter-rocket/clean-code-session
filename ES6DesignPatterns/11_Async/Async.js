
// Promise
const makeRequest = () => {
  try {
    getJSON()
      .then(result => {
        // this parse may fail
        const data = JSON.parse(result);
        console.log(data);
      })
      .catch((err) => {
        throw new Error(err);
      })
  } catch (err) {
    console.log(err);
  }
};

// Async/await
const makeRequest = async () => {
  try {
    // this parse may fail
    const data = JSON.parse(await getJSON());
    console.log(data);
  } catch (err) {
    console.log(err);
  }
};

//  run parallel async
const indexAction = asyncMiddleware(async (request, response) => {
  const oxfordData = oxfordService.getWord(request.params.word);
  const wiktionaryData = wiktionaryService.getWord(request.params.word);

  const results = { await wiktionaryData, await oxfordData };
  // ...
});
