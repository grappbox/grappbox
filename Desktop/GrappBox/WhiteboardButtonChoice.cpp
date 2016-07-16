#include <QMessageBox>
#include "WhiteboardButtonChoice.h"

WhiteboardButtonChoice::WhiteboardButtonChoice(Whiteboard w, QWidget *parent) : QWidget(parent)
{
	_WhiteboardID = w;

    _MainLayout = new QVBoxLayout();

    _EditButton = new QPushButton("Edit");
    _NameWhiteboard = new QLabel(w.name);

    _CreateDate = new QLabel("Created: " + w.creation.toString("yyyy-MM-dd HH:mm"));
    _EditDate = new QLabel("Updated: " + w.lastModification.date().toString("yyyy-MM-dd HH:mm"));

    _MainLayout->addWidget(_NameWhiteboard, 3);
    _MainLayout->addWidget(_CreateDate, 1);
    _MainLayout->addWidget(_EditDate, 1);
    _MainLayout->addWidget(_EditButton, 3);

	setFixedSize(300, 200);

    setLayout(_MainLayout);
    connect(_EditButton, SIGNAL(clicked(bool)), this, SLOT(Edit()));

	setFixedSize(300, 200);
}

void WhiteboardButtonChoice::Edit()
{
    emit OnEdit(_WhiteboardID.id);
}

void WhiteboardButtonChoice::Delete()
{
	emit OnDelete(_WhiteboardID.id);
}