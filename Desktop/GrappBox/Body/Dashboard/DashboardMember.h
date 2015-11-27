#ifndef DASHBOARDMEMBER_H
#define DASHBOARDMEMBER_H

#include <QtWidgets/QWidget>
#include <QImage>
#include <QtWidgets/QLabel>
#include <QtWidgets/QPushButton>
#include <QtWidgets/QHBoxLayout>
#include <QtWidgets/QVBoxLayout>
#include <QtWidgets/QGridLayout>
#include <QtWidgets/QStyleOption>
#include <QPainter>

#include "DashboardInformation.h"

class DashboardMember : public QWidget
{
    Q_OBJECT
public:
    explicit DashboardMember(DashboardInformation::MemberAvaiableInfo *info, QWidget *parent = 0, int userId = 0);
    void paintEvent(QPaintEvent *);
signals:

public slots:

private:
    int             _UserId;

//Widget
private:
    // Overlay


    // Base card
    QVBoxLayout     *_MainLayout;
    QGridLayout     *_StateLayout;

    QImage          *_MemberPicture;
    QLabel          *_MemberPictureDrawer;
    QLabel          *_MemberName;
    QLabel          *_BusyDrawer;
    QPushButton     *_AddTaskButton;
};

#endif // DASHBOARDMEMBER_H
