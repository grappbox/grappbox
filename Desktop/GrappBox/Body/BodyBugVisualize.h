#ifndef BODYBUGVISUALIZE_H
#define BODYBUGVISUALIZE_H

#include "BugTracker/IBugPage.h"
#include "BugTracker/BugViewTitleWidget.h"
#include "BugTracker/BugViewStatusBar.h"
#include "BugTracker/BugViewPreviewWidget.h"
#include "BugTracker/BugViewCategoryWidget.h"
#include "BugTracker/BugViewAssigneeWidget.h"
#include <QWidget>
#include <QVBoxLayout>
#include <QHBoxLayout>
#include <QScrollArea>
#include <QList>
#include <QJsonObject>
#include <QJsonArray>
#include <QMessageBox>

#define UNUSED __attribute__((unused))
#define PH_ASSIGNATION  tr("Assign")
#define PH_BACK         tr("Back")

#define COMMENTBOX_HEIGHT     300

#define JSON_AVATAR         "avatar"
#define JSON_ID             "id"
#define JSON_USERID         "creatorId"
#define JSON_COMMENTOR      "name"
#define JSON_COMMENT        "description"
#define JSON_DATE           "date"
#define JSON_TITLE          "title"

class BodyBugVisualize : public QWidget, public IBugPage
{
    Q_OBJECT
public:
    explicit                BodyBugVisualize(QWidget *parent = 0);
    virtual void            Show(BodyBugTracker *pageManager, QJsonObject *data);
    virtual void            Hide();
    void                    DeleteComments();
    void                    AddCommentsAtStart(const QList<QJsonObject> &comments);

signals:
    void                    OnLoadingDone(BodyBugTracker::BugTrackerPage page);

public slots:
    void                    TriggerCategoryBtnReleased();
    void                    TriggerAssigneeBtnReleased();
    void                    TriggerIssueClosed(int bugId);
    void                    TriggerCommentButtonReleased(BugViewPreviewWidget*);
    void                    TriggerGotComments(int id, QByteArray data);
    void                    TriggerPushCommentSuccess(int id, QByteArray data);
    void                    TriggerAPIFailure(int id, QByteArray data);
    void                    TriggerGotUserProject(int id, QByteArray data);
    void                    TriggerGotBug(int id, QByteArray data);

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
    QJsonObject             _bugData;
};

#endif // BODYBUGVISUALIZE_H
