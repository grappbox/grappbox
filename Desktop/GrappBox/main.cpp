#include "MainWindow.h"
#include "SStyleLoader.h"
#include <QtWidgets/QApplication>
#include <QScrollArea>

int main(int argc, char *argv[])
{
    QApplication a(argc, argv);

	a.setStyleSheet(SStyleLoader::LoadStyleSheet(BASE));

    MainWindow w;

    return a.exec();
}
