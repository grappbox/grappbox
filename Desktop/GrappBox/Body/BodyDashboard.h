#ifndef BODYDASHBOARD_H
#define BODYDASHBOARD_H

#include "IBodyContener.h"
#include "Dashboard\DashboardMember.h"
#include "Dashboard\DashboardGlobalProgress.h"
#include "Dashboard\DashboardMeeting.h"

#include <QtWidgets/QWidget>
#include <QtWidgets/QLabel>
#include <QtWidgets/QVBoxLayout>
#include <QtWidgets/QHBoxLayout>

class BodyDashboard : public QWidget, public IBodyContener
{
    Q_OBJECT
public:
    explicit BodyDashboard(QWidget *parent = 0);
    virtual void Show(int ID, MainWindow *mainApp);
    virtual void Hide();

signals:

public slots:
    /*void Failure(int, QByteArray);
    void GetAllProject(int, QByteArray);
    void NextMeeting(int, QByteArray);*/

private:
    int                 _ProjectId;
    MainWindow          *_MainApplication;

    //Widgets
private:

    //Widget if project loaded
    QVBoxLayout         *_MainLayoutLoaded;
    QLabel              *_TitleMemberAvaible;
    QHBoxLayout         *_MemberAvaible;
    QLabel              *_TitleNextMeeting;
    QHBoxLayout         *_NextMeeting;
    QLabel              *_TitleGlobalProgress;
    QHBoxLayout         *_GlobalProgress;
};

#endif // BODYDASHBOARD_H
