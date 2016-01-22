#ifndef CALENDAREVENTFORM_H
#define CALENDAREVENTFORM_H

#include <QDialog>
#include <QLineEdit>
#include <QTextEdit>
#include <QDateEdit>
#include <QTimeEdit>
#include <QFormLayout>
#include <QHBoxLayout>
#include <QPushButton>
#include "Calendar/CalendarEvent.h"

class CalendarEventForm : public QDialog
{
    Q_OBJECT
public:
    CalendarEventForm(Event *event);

signals:

public slots:
    void OnSave();
    void OnRemove();

private:
    Event *_CurrentEvent;

    QFormLayout *_MainLayout;

    QLineEdit *_TitleEdit;

    QHBoxLayout *_DateStartLayout;
    QDateEdit *_DateStart;
    QTimeEdit *_TimeStart;

    QHBoxLayout *_DateEndLayout;
    QDateEdit *_DateEnd;
    QTimeEdit *_TimeEnd;

    QTextEdit *_DescriptionEdit;

    QHBoxLayout *_Buttons;
    QPushButton *_Save;
    QPushButton *_Remove;
};

#endif // CALENDAREVENTFORM_H
