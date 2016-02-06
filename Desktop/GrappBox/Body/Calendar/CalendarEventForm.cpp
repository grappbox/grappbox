#include <QMessageBox>
#include <QDebug>
#include <QXmlStreamReader>
#include <QJsonDocument>
#include <QJsonObject>
#include <QJsonArray>
#include "SDataManager.h"
#include "CalendarEventForm.h"
#include "utils.h"
#include "ListAddRemoveSelection.h"

CalendarEventForm::CalendarEventForm(Event *event, QMap<int, QString> &project, QWidget *callBackEvent)
{
	_CurrentEvent = event;
	_CallBackWidget = callBackEvent;

	_MainLayout = new QFormLayout();
	_DateStartLayout = new QHBoxLayout();
	_DateEndLayout = new QHBoxLayout();
	_Buttons = new QHBoxLayout();

	_TitleEdit = new QLineEdit();
	_DescriptionEdit = new QTextEdit();
	_DateStart = new QDateEdit();
	_DateStart->setCalendarPopup(true);
	_TimeStart = new QTimeEdit();
	_DateEnd = new QDateEdit();
	_DateEnd->setCalendarPopup(true);
	_TimeEnd = new QTimeEdit();

	_SelectionProject = new QComboBox();
	for (QMap<int, QString>::iterator it = project.begin(); it != project.end(); ++it)
	{
		_SelectionProject->addItem(it.value(), QVariant(it.key()));
	}
	_SelectionProject->setCurrentIndex(0);

	_DateStartLayout->addWidget(_DateStart, 4);
	_DateStartLayout->addWidget(_TimeStart, 4);
	_DateStartLayout->addWidget(new QLabel(" to "), 1);
	_DateStartLayout->addWidget(_DateEnd, 4);
	_DateStartLayout->addWidget(_TimeEnd, 4);

	_Area = new QScrollArea();
	QWidget *userWidget = new QWidget();
	_UserAssociated = new QVBoxLayout();
	_UserAssociated->setSpacing(0);
	_UserAssociated->setContentsMargins(0, 0, 0, 0);
	userWidget->setLayout(_UserAssociated);
	_Area->setWidget(userWidget);
	_Area->setWidgetResizable(true);
	_Area->setMinimumHeight(300);

	_Save = new QPushButton("Save");
	_Remove = new QPushButton("Delete event");

	_Buttons->addWidget(_Save);
	_Buttons->addWidget(_Remove);

	_MainLayout->addRow(_TitleEdit);
	_MainLayout->addRow(_DateStartLayout);
	_MainLayout->addRow(new QLabel("Event Detail"));
	_MainLayout->addRow("Description", _DescriptionEdit);
	_MainLayout->addRow("Project", _SelectionProject);
	_MainLayout->addRow(new QLabel("Participants"));
	_MainLayout->addRow(_Area);
	_MainLayout->addRow(_Buttons);

	setLayout(_MainLayout);

	QObject::connect(_SelectionProject, SIGNAL(currentIndexChanged(int)), this, SLOT(OnProjectSelected()));
	QObject::connect(_Save, SIGNAL(clicked(bool)), this, SLOT(OnSave()));
	QObject::connect(_Remove, SIGNAL(clicked(bool)), this, SLOT(OnRemove()));
	QObject::connect(_UserAssociated, SIGNAL(itemSelectionChanged()), this, SLOT(OnListUserSelected()));
	
	_EventLoaded = true;

	if (event)
	{
		_EventLoaded = false;
		_TitleEdit->setText(event->Title);
		_DateStart->setDate(event->Start.date());
		_TimeStart->setTime(event->Start.time());
		_DateEnd->setDate(event->End.date());
		_TimeStart->setTime(event->End.time());
		_DescriptionEdit->setText(event->Description);
		setDisabled(true);
		QVector<QString> data;
		for (QMap<int, QString>::iterator it = project.begin(); it != project.end(); ++it)
		{
			data.push_back(USER_TOKEN);
			data.push_back(TO_STRING(it.key()));
			int id = DATA_CONNECTOR->Get(API::DP_PROJECT, API::GR_PROJECT_USERS_ALL, data, this, "OnLoadProjectUserDone", "OnLoadProjectUserFail");
			data.clear();
			_PendingCallProject[id] = it.key();
		}
		data.push_back(USER_TOKEN);
		data.push_back(TO_STRING(event->EventId));
		DATA_CONNECTOR->Get(API::DP_CALENDAR, API::GR_EVENT, data, this, "OnLoadEventDone", "OnLoadEventFail");
		int currentIndex = _SelectionProject->findData(QVariant(event->ProjectId));
		if (currentIndex != -1)
			_SelectionProject->setCurrentIndex(currentIndex);
	}
}

void CalendarEventForm::OnSave()
{
	// Launch call API
}

void CalendarEventForm::OnRemove()
{
	if (QMessageBox::warning(this, "Delete event", "Area you sure you want to delete this event ?", QMessageBox::Yes, QMessageBox::No) == QMessageBox::Yes)
	{
		qDebug() << "Delete !";
		close();
	}
}

void CalendarEventForm::OnLoadProjectUserDone(int id, QByteArray data)
{
	QJsonDocument doc = QJsonDocument::fromJson(data);

	int projectId = _PendingCallProject[id];

	qDebug() << doc.toJson(QJsonDocument::Indented);

	QJsonObject obj = doc.object()["data"].toObject();
	QJsonArray arr = obj["array"].toArray();
	for (QJsonValueRef item : arr)
	{
		QJsonObject curObj = item.toObject();
		QString name = curObj["firstname"].toString() + " " + curObj["lastname"].toString();
		_UserProjectsList[projectId].push_back(QPair<int, QString>(curObj["id"].toInt(), name));
	}
	_PendingCallProject.remove(id);
	EndLoad();
}

void CalendarEventForm::OnLoadProjectUserFail(int id, QByteArray data)
{
}

void CalendarEventForm::OnLoadEventDone(int id, QByteArray data)
{
	QJsonDocument doc = QJsonDocument::fromJson(data);

	QJsonObject obj = doc.object()["data"].toObject();
	QJsonArray arr = obj["users"].toArray();
	for (QJsonValueRef item : arr)
	{
		QJsonObject curObj = item.toObject();
		_AssociatedUserForProject[_CurrentEvent->ProjectId].push_back(curObj["id"].toInt());
	}
	_EventLoaded = true;
	EndLoad();
}

void CalendarEventForm::OnLoadEventFail(int id, QByteArray data)
{
}

void CalendarEventForm::OnSaveAssociatedDone(int id, QByteArray data)
{
	// Launch save event
}

void CalendarEventForm::OnSaveAssociatedFail(int id, QByteArray data)
{
	setDisabled(false);
	close();
}

void CalendarEventForm::OnSaveEventDone(int id, QByteArray data)
{
	setDisabled(false);
	close();
}

void CalendarEventForm::OnSaveEventFail(int id, QByteArray data)
{
	setDisabled(false);
	close();
}

void CalendarEventForm::OnRemoveEventDone(int id, QByteArray data)
{
	setDisabled(false);
	close();
}

void CalendarEventForm::OnRemoveEventFail(int id, QByteArray data)
{
	setDisabled(false);
	close();
}

void CalendarEventForm::OnListUserSelected(int id)
{
	int selectedProject = _SelectionProject->currentData().toInt();
	if (_AssociatedUserForProject[selectedProject].contains(id))
		_AssociatedUserForProject[selectedProject].removeAll(id);
	else
		_AssociatedUserForProject[selectedProject].push_back(id);
	EndLoad(false);
}

void CalendarEventForm::OnProjectSelected()
{
	EndLoad(false);
}

void CalendarEventForm::EndLoad(bool checkAPILoad)
{
	if (checkAPILoad && (_PendingCallProject.size() > 0 || !_EventLoaded))
	{
		return;
	}
	int selectedProject = _SelectionProject->currentData().toInt();
	while (QLayoutItem *item = _UserAssociated->takeAt(0))
	{
		if (item->widget())
			delete item->widget();
		delete item;
	}
	QList<QPair<int, QString> > added;
	QList<QPair<int, QString> > notAdded;
	QPair<int, QString> owner;
	for (QPair<int, QString> item : _UserProjectsList[selectedProject])
	{
		if (item.first == USER_ID)
		{
			owner = item;
			continue;
		}
		if (_AssociatedUserForProject[selectedProject].contains(item.first))
			added.push_back(item);
		else
			notAdded.push_back(item);
	}
	QLabel *addedLabel = new QLabel("Added");
	_UserAssociated->addWidget(addedLabel);
	_UserAssociated->addWidget(new QLabel(owner.second));
	for (QPair<int, QString> item : added)
	{
		ListAddRemoveSelection *itemW = new ListAddRemoveSelection(item.first, false, item.second);
		_UserAssociated->addWidget(itemW);
		QObject::connect(itemW, SIGNAL(Selected(int)), this, SLOT(OnListUserSelected(int)));
		
	}
	QLabel *removedLabel = new QLabel("Not added");
	_UserAssociated->addWidget(removedLabel);
	for (QPair<int, QString> item : notAdded)
	{
		ListAddRemoveSelection *itemW = new ListAddRemoveSelection(item.first, true, item.second);
		_UserAssociated->addWidget(itemW);
		QObject::connect(itemW, SIGNAL(Selected(int)), this, SLOT(OnListUserSelected(int)));
	}
	setDisabled(false);
}
