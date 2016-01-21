#include "MainWindow.h"
#include "PushButtonImage.h"
#include "BodyCalendar.h"
#include <QtWidgets/QApplication>
#include <QScrollArea>

int main(int argc, char *argv[])
{
    QApplication a(argc, argv);

    BodyCalendar *day = new BodyCalendar();
    day->show();
    day->setFixedSize(1024, 786);

    return a.exec();


    MainWindow w;

    return a.exec();
}
