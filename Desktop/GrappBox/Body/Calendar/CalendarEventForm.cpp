#include <QMessageBox>
#include <QDebug>
#include "CalendarEventForm.h"

CalendarEventForm::CalendarEventForm(Event *event)
{
    _CurrentEvent = event;

    _MainLayout = new QFormLayout();
    _DateStartLayout = new QHBoxLayout();
    _DateEndLayout = new QHBoxLayout();
    _Buttons = new QHBoxLayout();

    _TitleEdit = new QLineEdit();
    _DescriptionEdit = new QTextEdit();
    _DateStart = new QDateEdit();
    _TimeStart = new QTimeEdit();
    _DateEnd = new QDateEdit();
    _TimeEnd = new QTimeEdit();

    _DateStartLayout->addWidget(_DateStart);
    _DateStartLayout->addWidget(_TimeStart);
    _DateEndLayout->addWidget(_DateEnd);
    _DateEndLayout->addWidget(_TimeEnd);

    _Save = new QPushButton("Save");
    _Remove = new QPushButton("Remove");

    _Buttons->addWidget(_Save);
    _Buttons->addWidget(_Remove);

    _MainLayout->addRow("Title", _TitleEdit);
    _MainLayout->addRow("Start date", _DateStartLayout);
    _MainLayout->addRow("End date", _DateEndLayout);
    _MainLayout->addRow("Description", _DescriptionEdit);
    _MainLayout->addRow(_Buttons);

    setLayout(_MainLayout);

    QObject::connect(_Save, SIGNAL(clicked(bool)), this, SLOT(OnSave()));
    QObject::connect(_Remove, SIGNAL(clicked(bool)), this, SLOT(OnRemove()));
}

void CalendarEventForm::OnSave()
{
    close();
}

void CalendarEventForm::OnRemove()
{
    if (QMessageBox::warning(this, "Delete event", "Area you sure you want to delete this event ?", QMessageBox::Yes, QMessageBox::No) == QMessageBox::Yes)
    {
        qDebug() << "Delete !";
        close();
    }
}
