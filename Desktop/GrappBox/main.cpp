#include "MainWindow.h"
#include <QApplication>

#include <QDebug>
#include <QStandardItemModel>
#include <QStandardItem>
#include <QTableView>
#include <QComboBox>
#include <QHeaderView>

int main(int argc, char *argv[])
{
    QApplication a(argc, argv);

    MainWindow w;
    w.show();

    return a.exec();
}
