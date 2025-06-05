import 'package:flutter/material.dart';

class SuhuSelector extends StatelessWidget {
  final String? selectedSuhu;
  final Function(String) onSelected;

  const SuhuSelector({
    Key? key,
    required this.selectedSuhu,
    required this.onSelected,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final suhuOptions = ['panas', 'dingin'];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          "Pilih Versi:",
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 10),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
          children: suhuOptions.map((suhu) {
            final isSelected = selectedSuhu == suhu;
            return GestureDetector(
              onTap: () => onSelected(suhu),
              child: Card(
                color: isSelected ? Colors.blue[100] : Colors.white,
                elevation: 3,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                  side: BorderSide(
                    color: isSelected ? Colors.blue : Colors.grey,
                  ),
                ),
                child: Padding(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 20, vertical: 15),
                  child: Text(
                    suhu.toUpperCase(),
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: isSelected ? Colors.blue : Colors.black,
                    ),
                  ),
                ),
              ),
            );
          }).toList(),
        ),
      ],
    );
  }
}
