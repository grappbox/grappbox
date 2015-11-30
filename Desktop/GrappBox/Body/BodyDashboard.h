#ifndef BODYDASHBOARD_H
#define BODYDASHBOARD_H

#include "IBodyContener.h"
#include "Dashboard\DashboardMember.h"
#include "Dashboard\DashboardGlobalProgress.h"
#include "Dashboard\DashboardMeeting.h"
#include "Dashboard/DashboardInformation.h"

#include <QtWidgets/QWidget>
#include <QtWidgets/QLabel>
#include <QtWidgets/QVBoxLayout>
#include <QtWidgets/QHBoxLayout>
#include <QtWidgets/QScrollArea>
#include <QMap>

using namespace DashboardInformation;

class BodyDashboard : public QWidget, public IBodyContener
{
    Q_OBJECT
public:
    explicit BodyDashboard(QWidget *parent = 0);
    virtual void Show(int ID, MainWindow *mainApp);
    virtual void Hide();

signals:
    void OnLoadingDone();

public slots:
    void Failure(int, QByteArray);
    void GetMemberProject(int, QByteArray);
    void GetNextMeeting(int, QByteArray);
    void GetAllProject(int, QByteArray);
    void GetProjectsId(int, QByteArray);

private:
    void UpdateLayout(bool sendSignal = true);
    void DeleteLayout();

    int                 _UserId;
    MainWindow          *_MainApplication;

    //Widgets
private:

    //Widget if project loaded
    QVBoxLayout         *_MainLayoutLoaded;
    QLabel              *_TitleMemberAvaible;
    QHBoxLayout         *_MemberAvaible;
    QScrollArea         *_ScrollMember;
    QLabel              *_TitleNextMeeting;
    QHBoxLayout         *_NextMeeting;
    QLabel              *_TitleGlobalProgress;
    QHBoxLayout         *_GlobalProgress;
    QScrollArea         *_ScrollProgress;

    QWidget             *_TopWidget;
    QWidget             *_MiddleWidget;
    QWidget             *_BottomWidget;

    //Information API
    bool                                _IsInitializing;
    int                                 _NumberBeforeInitializingDone;

    QMap<int, MemberAvaiableInfo*>      _MemberData;
    QMap<int, NextMeetingInfo*>         _NextMeetingData;
    QMap<int, GlobalProgressInfo*>      _GlobalProgressData;
    QMap<int, int>                      _ProjectIdsData;
    QMap<int, int>                      _ProjectCreatorIdsData;
};

#endif // BODYDASHBOARD_H
