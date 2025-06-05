import 'package:cloud_firestore/cloud_firestore.dart';

class DatabaseService {
  final FirebaseFirestore _db = FirebaseFirestore.instance;

  Future<void> saveUser(
      String userId, String phoneNumber, String username) async {
    try {
      await _db.collection('users').doc(userId).set({
        'phoneNumber': phoneNumber,
        'username': username,
      });
    } catch (e) {
      print("Error saving user: $e");
    }
  }

  Future<bool> checkUserExists(String phoneNumber) async {
    try {
      QuerySnapshot snapshot = await _db
          .collection('users')
          .where('phoneNumber', isEqualTo: phoneNumber)
          .get();
      return snapshot.docs.isNotEmpty;
    } catch (e) {
      print("Error checking user existence: $e");
      return false;
    }
  }
}
