const admin = require("firebase-admin");
const fs = require("fs");

const serviceAccount = require("./cafedel-24717-firebase-adminsdk-fbsvc-903a90c41e.json");

admin.initializeApp({
  credential: admin.credential.cert(serviceAccount),
});

const db = admin.firestore();

const rawData = fs.readFileSync("delbites_firebase.json");
const jsonData = JSON.parse(rawData);

Object.keys(jsonData).forEach(async (collectionName) => {
  const collectionRef = db.collection(collectionName);

  for (let doc of jsonData[collectionName]) {
    await collectionRef.add(doc);
  }
});

console.log("Data berhasil diunggah ke Firestore!");
