#include "BodyBugCreation.h"

BodyBugCreation::BodyBugCreation(QWidget *parent) : QWidget(parent)
{
    QString style;
    QWidget *widgetTitleCategory = new QWidget();
    QWidget *widgetTitleAssignee = new QWidget();
    QLabel *lblTitleCategory = new QLabel(tr("Categories"));
    QLabel *lblTitleAssignee = new QLabel(tr("Assignee"));
    QHBoxLayout *layoutTitleCategory = new QHBoxLayout();
    QHBoxLayout *layoutTitleAssignee = new QHBoxLayout();
    QVBoxLayout *layoutCategoryArea = new QVBoxLayout();
    QVBoxLayout *layoutAssigneeArea = new QVBoxLayout();
    _btnCategoriesAssign = new QPushButton(PH_ASSIGNATION);
    _btnAssigneeAssign = new QPushButton(PH_ASSIGNATION);
    _bugId = -1;
    _mainLayout = new QVBoxLayout();
    _bodyLayout = new QHBoxLayout();
    _issueLayout = new QVBoxLayout();
    _sideMenuLayout = new QVBoxLayout();
    _titleBar = new BugViewTitleWidget("", true);
    _categories = new BugViewCategoryWidget();
    _assignees = new BugViewAssigneeWidget();
    _commentArea = new QScrollArea();
    _commentLayout = new QVBoxLayout();
    _categoriesArea = new QScrollArea();
    _assigneesArea = new QScrollArea();
    _waitingPageCreated = 0;

    _issueLayout->addWidget(_commentArea);

    widgetTitleCategory->setLayout(layoutTitleCategory);
    layoutTitleCategory->addWidget(lblTitleCategory);
    layoutTitleCategory->addWidget(_btnCategoriesAssign);

    widgetTitleAssignee->setLayout(layoutTitleAssignee);
    layoutTitleAssignee->addWidget(lblTitleAssignee);
    layoutTitleAssignee->addWidget(_btnAssigneeAssign);

    _categoriesArea->setMaximumWidth(250);
    _assigneesArea->setMaximumWidth(250);

    layoutAssigneeArea->addWidget(_assignees);
    layoutCategoryArea->addWidget(_categories);

    _categoriesArea->setLayout(layoutCategoryArea);
    _assigneesArea->setLayout(layoutAssigneeArea);

    _sideMenuLayout->addWidget(widgetTitleCategory);
    _sideMenuLayout->addWidget(_categoriesArea);
    _sideMenuLayout->addWidget(widgetTitleAssignee);
    _sideMenuLayout->addWidget(_assigneesArea);

    _commentArea->setLayout(_commentLayout);

    _bodyLayout->addLayout(_issueLayout);
    _bodyLayout->addLayout(_sideMenuLayout);

    QObject::connect(_btnAssigneeAssign, SIGNAL(released()), this, SLOT(TriggerAssigneeBtnReleased()));
    QObject::connect(_btnCategoriesAssign, SIGNAL(released()), this, SLOT(TriggerCategoryBtnReleased()));
    QObject::connect(_assignees, SIGNAL(OnPageItemsCreated(BugViewAssigneeWidget::BugAssigneePage)), this, SLOT(TriggerAssigneePageCreated(BugViewAssigneeWidget::BugAssigneePage)));
    QObject::connect(_categories, SIGNAL(OnPageItemsCreated(BugViewCategoryWidget::BugCategoryPage)), this, SLOT(TriggerCategoryPageCreated(BugViewCategoryWidget::BugCategoryPage)));

    _mainLayout->addWidget(_titleBar);
    _mainLayout->addLayout(_bodyLayout);
    this->setLayout(_mainLayout);

    _btnCategoriesAssign->hide();
    _btnAssigneeAssign->hide();
    _btnCategoriesAssign->setEnabled(false);
    _btnAssigneeAssign->setEnabled(false);
    _categories->TriggerOpenPage(BugViewCategoryWidget::BugCategoryPage::ASSIGN);
    _assignees->TriggerOpenPage(BugViewAssigneeWidget::BugAssigneePage::ASSIGN);
    _categories->DisableAPIAssignation(true);
    _assignees->DisableAPIAssignation(true);

    // [STYLE]
    style = "QPushButton{"
            "background-color: #595959;"
            "color : #ffffff;"
            "border-radius: 2px;"
            "min-width : 80px;"
            "min-height : 25px;"
            "max-width : 80px;"
            "max-height : 25px;"
            "font-size: 12px;"
            "font-weight: bold;"
            "}"
            "QPushButton:hover{"
            "background-color: #bababa;"
            "}"
            "QScrollArea{"
            "border: 0px;"
            "background-color: #FFFFFF;"
            "}"
            "QWidget#Title{"
            "border-bottom: 1px solid #000000;"
            "}"
            "QVBoxLayout#Issues{"
            "alternate-background-color: #a6a6a6;"
            "background: #d9d9d9;"
            "}";
    this->setStyleSheet(style);
}

void BodyBugCreation::Show(BodyBugTracker *pageManager, QJsonObject  *data)
{
    QString token;
    int currentProject;

    _bugId = -1;
    _mainApp = pageManager;
    _titleBar->SetBugID(_bugId);
    this->DeleteComments();

    token = API::SDataManager::GetDataManager()->GetToken();
    currentProject = API::SDataManager::GetDataManager()->GetCurrentProject();
    _waitingPageCreated = 2;

	BEGIN_REQUEST;
	{
		SET_ON_DONE("TriggerGotProjectUsers");
		SET_ON_FAIL("TriggerAPIFailure");
		SET_CALL_OBJECT(this);
		ADD_FIELD("token", token);
		ADD_FIELD("projectId", currentProject);
		GET(API::DP_BUGTRACKER, API::GR_PROJECT_USERS_ALL); //[CHIE DESSUS]
/*		SET_ON_DONE("TriggerGotProjectTags");
		GET(API::DP_BUGTRACKER, API::GR_PROJECTBUGTAG_ALL); //[CHIE DESSUS]
*/	}
	END_REQUEST;
}

void BodyBugCreation::Hide()
{
    _assignees->DeletePageItems(BugViewAssigneeWidget::BugAssigneePage::ASSIGN);
    _categories->DeletePageItems(BugViewCategoryWidget::BugCategoryPage::ASSIGN);
    hide();
}

void BodyBugCreation::TriggerAssigneeBtnReleased()
{
    if (_assignees->GetCurrentPage() == BugViewAssigneeWidget::BugAssigneePage::VIEW)
    {
        _assignees->TriggerOpenPage(BugViewAssigneeWidget::BugAssigneePage::ASSIGN);
        _btnAssigneeAssign->setText(PH_BACK);
    }
    else
    {
        _assignees->TriggerOpenPage(BugViewAssigneeWidget::BugAssigneePage::VIEW);
        _btnAssigneeAssign->setText(PH_ASSIGNATION);
    }
}

void BodyBugCreation::TriggerCategoryBtnReleased()
{
    if (_categories->GetCurrentPage() == BugViewCategoryWidget::BugCategoryPage::VIEW)
    {
        _categories->TriggerOpenPage(BugViewCategoryWidget::BugCategoryPage::ASSIGN);
        _btnCategoriesAssign->setText(PH_BACK);
    }
    else
    {
        _categories->TriggerOpenPage(BugViewCategoryWidget::BugCategoryPage::VIEW);
        _btnCategoriesAssign->setText(PH_ASSIGNATION);
    }
}

void BodyBugCreation::DeleteComments()
{
    QLayoutItem *currentItem;
    BugViewPreviewWidget *commentWidget = new BugViewPreviewWidget(API::SDataManager::GetDataManager()->GetUserId(), true, true);

    while ((currentItem = _commentLayout->itemAt(0)) != nullptr)
    {
        if (currentItem->widget())
            currentItem->widget()->setParent(nullptr);
        _commentLayout->removeItem(currentItem);
    }
    commentWidget->setFixedHeight(COMMENTBOX_HEIGHT);
    _commentLayout->addWidget(commentWidget);
    _commentLayout->setAlignment(commentWidget, Qt::AlignTop);
    _commentWidget = commentWidget;
    QObject::connect(_commentWidget, SIGNAL(OnCommented(BugViewPreviewWidget*)), this, SLOT(TriggerComment(BugViewPreviewWidget*)));
}

void BodyBugCreation::TriggerComment(BugViewPreviewWidget* previewWid)
{
    QString comment = _commentWidget->GetComment();

	BEGIN_REQUEST;
	{
		SET_ON_DONE("TriggerBugCreated");
		SET_ON_FAIL("TriggerAPIFailure");
		SET_CALL_OBJECT(this);
		ADD_FIELD("token", API::SDataManager::GetDataManager()->GetToken());
		ADD_FIELD("projectId", API::SDataManager::GetDataManager()->GetCurrentProject());
		ADD_FIELD("title", _titleBar->GetTitle());
		ADD_FIELD("description", comment);
		ADD_FIELD("stateId", BUGSTATE_OPEN);
		ADD_FIELD("stateName", "");
		POST(API::DP_BUGTRACKER, API::PR_CREATE_BUG);
	}
	END_REQUEST;
}

void BodyBugCreation::TriggerBugCreated(int  id, QByteArray data)
{
    QJsonObject json = QJsonDocument::fromJson(data).object()["data"].toObject();
    QString comment = _commentWidget->GetComment();
    QString commentTitle = _commentWidget->GetCommentTitle();
    QList<int> assignedUser = _assignees->GetAllAssignee();
    QList<int> assignedCategories = _categories->GetAllAssignee();
    int bugID = json["ticket"].toObject()["id"].toInt();
    _waitingPageCreated = -1;
    _bugId = bugID;

    for (QList<int>::iterator it = assignedCategories.begin(); it != assignedCategories.end(); ++it)
    {
		BEGIN_REQUEST;
		{
			SET_ON_DONE("DoNothing");
			SET_ON_FAIL("TriggerAPIFailure");
			SET_CALL_OBJECT(this);
			ADD_FIELD("token", API::SDataManager::GetDataManager()->GetToken());
			ADD_FIELD("bugId", bugID);
			ADD_FIELD("tagId", *it);
			PUT(API::DP_BUGTRACKER, API::PUTR_ASSIGNTAG); //[CHIE DESSUS]
		}
		END_REQUEST;
    }
	BEGIN_REQUEST;
	{
		SET_ON_DONE("DoNothing");
		SET_ON_FAIL("TriggerAPIFailure");
		SET_CALL_OBJECT(this);
		ADD_FIELD("bugId", bugID);
		ADD_FIELD("token", API::SDataManager::GetDataManager()->GetToken());
		ADD_ARRAY("toRemove");
		ADD_ARRAY("toAdd");
/*		for (QList<int>::iterator it = assignedUser.begin(); it != assignedUser.end(); ++it)
			ADD_FIELD_ARRAY(*it, "toAdd"); //[CHIE DESSUS]*/
		PUT(API::DP_BUGTRACKER, API::PUTR_ASSIGNUSER_BUG); //[CHIE DESSUS]
	}
	END_REQUEST;
	BEGIN_REQUEST;
	{
		SET_ON_DONE("TriggerBugCommented");
		SET_ON_FAIL("TriggerAPIFailure");
		SET_CALL_OBJECT(this);
		ADD_FIELD("projectId", API::SDataManager::GetDataManager()->GetCurrentProject());
		ADD_FIELD("token", API::SDataManager::GetDataManager()->GetToken());
		ADD_FIELD("title", commentTitle);
		ADD_FIELD("description", comment);
		ADD_FIELD("parentId", json["ticket"].toObject()["id"].toInt());
		POST(API::DP_BUGTRACKER, API::PR_COMMENT_BUG);
	}
	END_REQUEST;
}

void BodyBugCreation::TriggerGotProjectTags(int  id, QByteArray data)
{
    QList<QJsonObject> categoriesObjects;
    QJsonObject json = QJsonDocument::fromJson(data).object()["data"].toObject();
	QJsonArray arr = json["array"].toArray();

	for (QJsonArray::iterator it = arr.begin(); it != arr.end(); ++it)
    {
        QJsonObject current = (*it).toObject();

        current.insert("assigned", false);
        categoriesObjects.append(current);
    }
    _categories->CreateAssignPageItems(categoriesObjects);
}

void BodyBugCreation::TriggerGotProjectUsers(int  id, QByteArray data)
{
    QList<QJsonObject> usersObjects;
    QJsonObject json = QJsonDocument::fromJson(data).object()["data"].toObject();
	QJsonArray arr = json["array"].toArray();
    for (QJsonArray::iterator it = arr.begin(); it != arr.end(); ++it)
    {
        QJsonObject current = (*it).toObject();

        current.insert("assigned", false);
        usersObjects.append(current);
    }
    _assignees->CreateAssignPageItems(usersObjects);
}

void BodyBugCreation::TriggerBugCommented(int  id, QByteArray  data)
{
    QJsonObject *intent = new QJsonObject();


    intent->insert("id", _bugId);
    intent->insert("title", this->_titleBar->GetTitle());
    _mainApp->TriggerChangePage(BodyBugTracker::BugTrackerPage::BUGVIEW, intent);
}

void BodyBugCreation::TriggerAPIFailure(int  id, QByteArray  data)
{
    --_waitingPageCreated;
    if (_waitingPageCreated == 0)
        emit OnLoadingDone(BodyBugTracker::BUGCREATE);
    QMessageBox::critical(this, tr("Connexion to Grappbox server failed"), tr("We can't contact the GrappBox server, check your internet connexion and retry. If the problem persist, please contact grappbox team at the address problem@grappbox.com"));
}

void BodyBugCreation::TriggerAssigneePageCreated(BugViewAssigneeWidget::BugAssigneePage page)
{
    --_waitingPageCreated;
    if (_waitingPageCreated <= 0)
    {
        _waitingPageCreated = 0;
        emit OnLoadingDone(BodyBugTracker::BUGCREATE);
    }
}

void BodyBugCreation::TriggerCategoryPageCreated(BugViewCategoryWidget::BugCategoryPage page)
{
    --_waitingPageCreated;
    if (_waitingPageCreated <= 0)
    {
        _waitingPageCreated = 0;
        emit OnLoadingDone(BodyBugTracker::BUGCREATE);
    }
}

void BodyBugCreation::DoNothing(int  id, QByteArray  data){}
