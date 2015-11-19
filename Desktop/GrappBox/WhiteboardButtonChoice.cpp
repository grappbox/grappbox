#include <sstream>
#include "WhiteboardButtonChoice.h"

WhiteboardButtonChoice::WhiteboardButtonChoice(int whiteboardId, BodyDashboard *mainApp, QWidget *parent) : QWidget(parent), _WhiteboardID(whiteboardId)
{
    _MainLayout = new QVBoxLayout();

    _EditButton = new QPushButton("Edit");
    std::ostringstream iss("");
    iss << _WhiteboardID;
    _NameWhiteboard = new QLabel(QString("Whiteboard #") + QString(iss.str().data()));

    _EditTime = new QLabel("Time : ##:##");
    _EditDate = new QLabel("Date : ####-##-##");

    _MainLayout->addWidget(_NameWhiteboard, 3);
    _MainLayout->addWidget(_EditTime, 1);
    _MainLayout->addWidget(_EditDate, 1);
    _MainLayout->addWidget(_EditButton, 3);

    setLayout(_MainLayout);
    connect(_EditButton, SIGNAL(clicked(bool)), this, SLOT(Edit()));

}

void WhiteboardButtonChoice::Edit()
{
    emit OnEdit(_WhiteboardID);
}
