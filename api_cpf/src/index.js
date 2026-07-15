const app = require("./routes");

const PORT = process.env.PORT || 3333;
app.listen(PORT, () => console.log(`Server running at ${PORT}`));
