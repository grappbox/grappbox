#include "MainWindow.h"
#include "PushButtonImage.h"
#include "BodyCalendar.h"
#include <QtWidgets/QApplication>
#include <QScrollArea>

int main(int argc, char *argv[])
{
    QApplication a(argc, argv);
    MainWindow w;

    return a.exec();
}
