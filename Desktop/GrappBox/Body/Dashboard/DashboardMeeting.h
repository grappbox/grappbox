#ifndef DASHBOARDMEETING_H
#define DASHBOARDMEETING_H

#include "DashboardInformation.h"

#include <QWidget>

#include <QHBoxLayout>
#include <QVBoxLayout>
#include <QLabel>
#include <QStyleOption>
#include <QPainter>

class DashboardMeeting : public QWidget
{
    Q_OBJECT
public:
    explicit DashboardMeeting(DashboardInformation::NextMeetingInfo *info, QWidget *parent = 0);
    void paintEvent(QPaintEvent *);
signals:

public slots:

// Widget
private:

    QHBoxLayout     *_HeadLayout;
    QVBoxLayout     *_MainLayout;
    QHBoxLayout     *_FooterLayout;

    QLabel          *_ProjectIcon;
    QLabel          *_MeetingName;
    QLabel          *_TypeIcon;

    QLabel          *_CalendarIcon;
    QLabel          *_Date;
    QLabel          *_HourIcon;
    QLabel          *_Hours;
};

#endif // DASHBOARDMEETING_H
