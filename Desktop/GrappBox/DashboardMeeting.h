#ifndef DASHBOARDMEETING_H
#define DASHBOARDMEETING_H

#include <QWidget>

#include <QHBoxLayout>
#include <QVBoxLayout>
#include <QLabel>

class DashboardMeeting : public QWidget
{
    Q_OBJECT
public:
    explicit DashboardMeeting(QWidget *parent = 0);

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
