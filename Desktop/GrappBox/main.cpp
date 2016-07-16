#include "MainWindow.h"
#include "SStyleLoader.h"
#include <QtWidgets/QApplication>
#include <QScrollArea>
#include "SDebugWindow.h"

int main(int argc, char *argv[])
{
    QApplication a(argc, argv);

	a.setStyleSheet(SStyleLoader::LoadStyleSheet(BASE));

    MainWindow w;
	SDebugWindow::GetInstance();
    return a.exec();
}
