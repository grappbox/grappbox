#ifndef CALENDAREVENTMONTH_H
#define CALENDAREVENTMONTH_H

#include "Calendar/CalendarEvent.h"

#include <QPaintEvent>
#include <QtGui>
#include <QStyleOption>
#include <QPainter>

#include <QWidget>

class CalendarEventMonth : public QWidget
{
    Q_OBJECT
public:
    explicit CalendarEventMonth(QDate date, QWidget *parent = 0);
    void LoadEvents(const QList<Event*> &event, QDate date);
    void HideProject(int id);
    void ShowProject(int id);

protected:
    virtual void paintEvent(QPaintEvent *event);

signals:

public slots:

private:
    QDate _Date;
    QList<Event*> _Events;

    QVBoxLayout *_MainLayout;
    QVBoxLayout *_EventLayout;

    QLabel *_DayNumberLabel;

    QList<int> _HidedProject;
};

#endif // CALENDAREVENTMONTH_H
