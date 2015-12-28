#include "BodyBugVisualize.h"

BodyBugVisualize::BodyBugVisualize(QWidget *parent) : QWidget(parent)
{
    _bugId = -1;
    _mainLayout = new QVBoxLayout();
    _bodyLayout = new QHBoxLayout();
    _issueLayout = new QVBoxLayout();
    _sideMenuLayout = new QVBoxLayout();
    _titleBar = new BugViewTitleWidget("");
    _statusBar = new BugViewStatusBar();
    _categories = new BugViewCategoryWidget();
    _assignees = new BugViewAssigneeWidget();
}

void BodyBugVisualize::Show(BodyBugTracker *pageManager, QJsonObject *data)
{
    _bugId = (*data)["id"].toInt();
    _mainApp = pageManager;
    _titleBar->SetBugID(_bugId);
    _titleBar->SetTitle((*data)["title"].toString());
    //TODO: Link API
    //Start Fake Data

    //End Fake Data
    emit OnLoadingDone(BodyBugTracker::BUGVIEW);
}

void BodyBugVisualize::Hide()
{
    hide();
}
