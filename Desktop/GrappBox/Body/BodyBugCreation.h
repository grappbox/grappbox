#ifndef BODYBUGCREATION_H
#define BODYBUGCREATION_H

#include "BugTracker/IBugPage.h"
#include "BugTracker/BugViewTitleWidget.h"
#include "BugTracker/BugViewStatusBar.h"
#include "BugTracker/BugViewPreviewWidget.h"
#include "BugTracker/BugViewCategoryWidget.h"
#include "BugTracker/BugViewAssigneeWidget.h"
#include "SDataManager.h"
#include <QWidget>
#include <QVBoxLayout>
#include <QHBoxLayout>
#include <QScrollArea>
#include <QList>
#include <QJsonObject>

#define UNUSED __attribute__((unused))
#define PH_ASSIGNATION  tr("Assign")
#define PH_BACK         tr("Back")

#define COMMENTBOX_HEIGHT     300

#define JSON_AVATAR         "avatar"
#define JSON_ID             "id"
#define JSON_COMMENTOR      "name"
#define JSON_COMMENT        "comment"
#define JSON_DATE           "date"

#define BUGSTATE_OPEN       1

class BodyBugCreation : public QWidget, public IBugPage
{
    Q_OBJECT
public:
    explicit                BodyBugCreation(QWidget *parent = 0);
    virtual void            Show(BodyBugTracker *pageManager, QJsonObject *data);
    virtual void            Hide();
    void                    DeleteComments();

signals:
    void                    OnLoadingDone(BodyBugTracker::BugTrackerPage page);

public slots:
    void                    TriggerCategoryBtnReleased();
    void                    TriggerAssigneeBtnReleased();
    void                    TriggerComment();
    void                    TriggerBugCreated(int id, QByteArray data);
    void                    TriggerBugCommented(int id, QByteArray data);
    void                    TriggerAPIFailure(int id, QByteArray data);

private:
    int                     _bugId;
    BodyBugTracker          *_mainApp;
    QVBoxLayout             *_mainLayout;
    QHBoxLayout             *_bodyLayout;
    QVBoxLayout             *_issueLayout;
    QVBoxLayout             *_sideMenuLayout;
    QVBoxLayout             *_commentLayout;
    BugViewTitleWidget      *_titleBar;
    BugViewStatusBar        *_statusBar;
    BugViewCategoryWidget   *_categories;
    BugViewAssigneeWidget   *_assignees;
    QScrollArea             *_commentArea;
    QScrollArea             *_categoriesArea;
    QScrollArea             *_assigneesArea;
    QPushButton             *_btnCategoriesAssign;
    QPushButton             *_btnAssigneeAssign;
    BugViewPreviewWidget    *_commentWidget;
};

#endif // BODYBUGCREATION_H
