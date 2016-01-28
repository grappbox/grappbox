#include <QtWidgets/QMessageBox>
#include <QException>
#include <QtNetwork/QNetworkRequest>
#include "DataConnectorOnline.h"
#include <QDebug>

using namespace API;

DataConnectorOnline::DataConnectorOnline()
{
	_Manager = new QNetworkAccessManager();
}

void DataConnectorOnline::OnResponseAPI()
{
	QNetworkReply *request = dynamic_cast<QNetworkReply*>(QObject::sender());
	qDebug() << "[DataConnectorOnline] Receive status code : " << request->attribute(QNetworkRequest::HttpStatusCodeAttribute).toString();
	qDebug() << "[DataConnectorOnline] Receive response from API with the url : " + request->request().url().toString();
	if (request == nullptr || !_Request.contains(request))
	{
		QMessageBox::critical(nullptr, "Critical error", "Unable to cast the reply of the API response.", QMessageBox::Ok);
		return;
	}
	QByteArray req = request->readAll();
	if (request->error())
	{
		qDebug() << "[DataConnectorOnline] Error with response : " << request->errorString();
		QMetaObject::invokeMethod(_CallBack[request]._Request, _CallBack[request]._SlotFailure, Q_ARG(int, _Request[request]), Q_ARG(QByteArray, req));
	}
	else
		QMetaObject::invokeMethod(_CallBack[request]._Request, _CallBack[request]._SlotSuccess, Q_ARG(int, _Request[request]), Q_ARG(QByteArray, req));
}

int DataConnectorOnline::Post(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure)
{
	QNetworkReply *reply = nullptr;
	switch (request)
	{
	case PR_LOGIN:
		reply = Login(data);
		break;
	case PR_ROLE_ADD:
		reply = AddRole(data);
		break;
	case PR_ROLE_ASSIGN:
		reply = AttachRole(data);
		break;
	case PR_CUSTOMER_GENERATE_ACCESS:
		reply = CustomerGenerateAccess(data);
		break;
	case PR_EDIT_MESSAGE_TIMELINE:
		reply = EditMessageTimeline(data);
		break;
	case PR_MESSAGE_TIMELINE:
		reply = PostMessageTimeline(data);
		break;
	case PR_CREATE_BUG:
		reply = OpenBug(data);
		break;
	case PR_COMMENT_BUG:
		reply = CommentBug(data);
		break;
	case PR_EDIT_BUG:
		reply = EditBug(data);
		break;
	case PR_ASSIGNUSER_BUG:
		reply = AssignUserToTicket(data);
		break;
	case PR_DELETEUSER_BUG:
		reply = DeleteUserToTicket(data);
		break;
	case PR_CREATETAG:
		reply = CreateTag(data);
		break;
	case PR_EDIT_COMMENTBUG:
		reply = EditCommentBug(data);
		break;
	case PR_POST_EVENT:
		reply = PostEvent(data);
		break;
	}
	if (reply == nullptr)
		throw QException();
	_CallBack[reply] = DataConnectorCallback();
	_CallBack[reply]._Request = requestResponseObject;
	_CallBack[reply]._SlotFailure = slotFailure;
	_CallBack[reply]._SlotSuccess = slotSuccess;
	int maxInt = 1;
	for (QMap<QNetworkReply*, int>::const_iterator it = _Request.constBegin(); it != _Request.constEnd(); ++it)
	{
		if (it.value() >= maxInt)
		{
			maxInt = it.value() + 1;
		}
	}
	_Request[reply] = maxInt;
	return maxInt;
}

int DataConnectorOnline::Put(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char *slotSuccess, const char *slotFailure)
{
	QNetworkReply *reply = nullptr;
	switch (request)
	{
	case PUTR_USERSETTINGS:
		reply = PutUserSettings(data);
		break;
	case PUTR_PROJECTSETTINGS:
		reply = PutProjectSettings(data);
		break;
	case PUTR_INVITE_USER:
		reply = ProjectInvite(data);
		break;
	case PUTR_ASSIGNTAG:
		reply = AssignTagToBug(data);
		break;
	case PUTR_EDIT_EVENT:
		reply = EditEvent(data);
		break;
	}
	if (reply == nullptr)
		throw QException();
	_CallBack[reply] = DataConnectorCallback();
	_CallBack[reply]._Request = requestResponseObject;
	_CallBack[reply]._SlotFailure = slotFailure;
	_CallBack[reply]._SlotSuccess = slotSuccess;
	int maxInt = 1;
	for (QMap<QNetworkReply*, int>::const_iterator it = _Request.constBegin(); it != _Request.constEnd(); ++it)
	{
		if (it.value() >= maxInt)
		{
			maxInt = it.value() + 1;
		}
	}
	_Request[reply] = maxInt;
	return maxInt;
}

int DataConnectorOnline::Get(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure)
{
	QNetworkReply *reply = nullptr;
	switch (request)
	{
	case GR_LOGOUT:
		reply = Logout(data);
		break;

	case GR_LIST_PROJECT:
		reply = GetActionOld("dashboard/getprojectsglobalprogress", data);
		break;

	case GR_PROJECT:
		reply = GetAction("projects/getinformations", data);
		break;

	case GR_CREATOR_PROJECT:
		reply = GetActionOld("dashboard/getprojectcreator", data);
		break;

	case GR_LIST_MEMBER_PROJECT:
		reply = GetActionOld("dashboard/getteamoccupation", data);
		break;

	case GR_LIST_MEETING:
		reply = GetActionOld("dashboard/getnextmeetings", data);
		break;
	case GR_USER_SETTINGS:
		reply = GetActionOld("user/basicinformations", data);
		break;
	case GR_PROJECTS_USER:
		reply = GetActionOld("user/getprojects", data);
		break;
	case GR_PROJECT_ROLE:
		reply = GetActionOld("roles/getprojectroles", data);
		break;
	case GR_PROJECT_USERS:
		reply = GetActionOld("dashboard/getprojectpersons", data);
		break;
	case GR_PROJECT_CANCEL_DELETE:
		reply = GetActionOld("projects/retrieveproject", data);
		break;
	case GR_PROJECT_USER_ROLE:
		reply = GetActionOld("roles/getrolebyprojectanduser", data);
		break;
	case GR_CUSTOMER_ACCESSES:
		reply = GetActionOld("projects/getcustomeraccessbyproject", data);
		break;
	case GR_CUSTOMER_ACCESS_BY_ID:
		reply = GetActionOld("projects/getcustomeraccessbyid", data);
		break;
	case GR_USERPROJECT_BUG:
		reply = GetActionOld("bugtracker/getticketsbyuser", data);
		break;
	case GR_XLAST_BUG_OFFSET:
		reply = GetActionOld("bugtracker/getlasttickets", data);
		break;
	case GR_XLAST_BUG_OFFSET_BY_STATE:
		reply = GetActionOld("bugtracker/getticketsbystate", data);
		break;
	case GR_XLAST_BUG_OFFSET_CLOSED:
		reply = GetActionOld("bugtracker/getlastclosedtickets", data);
		break;
	case GR_PROJECTBUG_ALL:
		reply = GetActionOld("bugtracker/gettickets", data);
		break;
	case GR_BUGCOMMENT:
		reply = GetActionOld("bugtracker/getcomments", data);
		break;
	case GR_GETBUGS_STATUS:
		reply = GetActionOld("bugtracker/getStates", data);
		break;
	case GR_LIST_TIMELINE:
		reply = GetActionOld("timeline/gettimelines", data);
		break;
	case GR_TIMELINE:
		reply = GetActionOld("timeline/getlastmessages", data);
		break;
	case GR_COMMENT_TIMELINE:
		reply = GetActionOld("timeline/getcomments", data);
		break;
	case GR_USER_DATA:
		reply = GetActionOld("user/getuserbasicinformations", data);
		break;
	case GR_ARCHIVE_MESSAGE_TIMELINE:
		reply = GetActionOld("timeline/archivemessage", data);
		break;
	case GR_PROJECTBUGTAG_ALL:
		reply = GetActionOld("bugtracker/getprojecttags", data);
		break;
	case GR_PROJECT_USERS_ALL:
		reply = GetActionOld("projects/getusertoproject", data);
		break;
	case GR_BUG:
		reply = GetActionOld("bugtracker/getticket", data);
		break;
	case GR_CALENDAR:
		reply = GetAction("planning/getmonth", data);
		break;
	}
	if (reply == nullptr)
		throw QException();
	_CallBack[reply] = DataConnectorCallback();
	_CallBack[reply]._Request = requestResponseObject;
	_CallBack[reply]._SlotFailure = slotFailure;
	_CallBack[reply]._SlotSuccess = slotSuccess;
	int maxInt = 1;
	for (QMap<QNetworkReply*, int>::const_iterator it = _Request.constBegin(); it != _Request.constEnd(); ++it)
	{
		if (it.value() >= maxInt)
		{
			maxInt = it.value() + 1;
		}
	}
	_Request[reply] = maxInt;
	return maxInt;
}

int DataConnectorOnline::Delete(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure)
{
	QNetworkReply *reply = nullptr;
	switch (request)
	{
	case DR_PROJECT_ROLE:
		reply = DeleteProjectRole(data);
		break;
	case DR_ROLE_DETACH:
		reply = DetachRole(data);
		break;
	case DR_PROJECT_USER:
		reply = DeleteProjectUser(data);
		break;
	case DR_PROJECT:
		reply = DeleteProject(data);
		break;
	case DR_CUSTOMER_ACCESS:
		reply = DeleteCustomerAccess(data);
		break;
	case DR_CLOSE_TICKET_OR_COMMENT:
		reply = RESTDelete(data, "bugtracker/closeticket");
		break;
	case DR_REMOVE_BUGTAG:
		reply = RESTDelete(data, "bugtracker/removetag");
		break;
	}
	if (reply == nullptr)
		throw QException();
	_CallBack[reply] = DataConnectorCallback();
	_CallBack[reply]._Request = requestResponseObject;
	_CallBack[reply]._SlotFailure = slotFailure;
	_CallBack[reply]._SlotSuccess = slotSuccess;
	int maxInt = 1;
	for (QMap<QNetworkReply*, int>::const_iterator it = _Request.constBegin(); it != _Request.constEnd(); ++it)
	{
		if (it.value() >= maxInt)
		{
			maxInt = it.value() + 1;
		}
	}
	_Request[reply] = maxInt;
	return maxInt;
}

QNetworkReply *DataConnectorOnline::Login(QVector<QString> &data)
{
	QJsonObject json;
	json["login"] = data[0];
	json["password"] = data[1];
	QJsonDocument doc(json);
	QByteArray jsonba = doc.toJson(QJsonDocument::Compact);

	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("accountadministration/login")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
	QNetworkReply *request = _Manager->post(requestSend, jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::PutUserSettings(QVector<QString> &data)
{
	QJsonObject json;
	QJsonObject birthdayJson;
	QTimeZone timezone;

	if (data[0] != "")
		json["first_name"] = data[0];
	if (data[1] != "")
		json["last_name"] = data[1];
	if (data[2] != "")
	{
		birthdayJson["date"] = data[2];
		birthdayJson["timezone_type"] = "3";
		birthdayJson["timezone"] = timezone.displayName(QTimeZone::StandardTime);
		json["birthday"] = birthdayJson;
	}

	if (data[3] != "")
		json["avatar"] = data[3];
	if (data[4] != "")
		json["phone"] = data[4];
	if (data[5] != "")
		json["country"] = data[5];
	if (data[6] != "")
		json["linkedin"] = data[6];
	if (data[7] != "")
		json["viadeo"] = data[7];
	if (data[8] != "")
		json["twitter"] = data[8];
	if (data[9] != "")
		json["password"] = data[9];

	QJsonDocument doc(json);
	QByteArray jsonba = doc.toJson(QJsonDocument::Compact);
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("user/basicinformations/") + data[10]));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
	QNetworkReply *request = _Manager->put(requestSend, jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::PutProjectSettings(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[0];
	json["projectId"] = data[1];
	if (data[2] != "")
		json["name"] = data[2];
	if (data[3] != "")
		json["description"] = data[3];
	if (data[4] != "")
		json["logo"] = data[4];
	if (data[5] != "")
		json["phone"] = data[5];
	if (data[6] != "")
		json["company"] = data[6];
	if (data[7] != "")
		json["email"] = data[7];
	if (data[8] != "")
		json["facebook"] = data[8];
	if (data[9] != "")
		json["twitter"] = data[9];
	if (data[10] != "")
		json["password"] = data[10];

	QJsonDocument doc(json);
	QByteArray jsonba = doc.toJson(QJsonDocument::Compact);
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("projects/updateinformations")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
	QNetworkReply *request = _Manager->put(requestSend, jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::Logout(QVector<QString> &data)
{
	QString url = OLD_URL_API + QString("accountadministration/logout");
	for (QVector<QString>::const_iterator it = data.constBegin(); it != data.constEnd(); ++it)
	{
		url += QString("/") + *it;
	}

	QNetworkReply *request = _Manager->get(QNetworkRequest(QUrl(url)));

	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));

	return request;
}

QNetworkReply * API::DataConnectorOnline::GetAction(QString urlIn, QVector<QString>& data)
{
	QString url = URL_API + urlIn;
	for (QVector<QString>::const_iterator it = data.constBegin(); it != data.constEnd(); ++it)
	{
		url += QString("/") + *it;
	}
	QNetworkReply *request = _Manager->get(QNetworkRequest(QUrl(url)));

	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));

	return request;
}

QNetworkReply *DataConnectorOnline::GetActionOld(QString urlIn, QVector<QString> &data)
{
	QString url = OLD_URL_API + urlIn;
	for (QVector<QString>::const_iterator it = data.constBegin(); it != data.constEnd(); ++it)
	{
		url += QString("/") + *it;
	}
	QNetworkReply *request = _Manager->get(QNetworkRequest(QUrl(url)));

	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));

	return request;
}

QNetworkReply *DataConnectorOnline::AddRole(QVector<QString> &data)
{
	QJsonObject json;

	json["_token"] = data[0];
	json["projectId"] = data[1];
	json["name"] = data[2];
	json["teamTimeline"] = data[3];
	json["customerTimeline"] = data[4];
	json["gantt"] = data[5];
	json["whiteboard"] = data[6];
	json["bugtracker"] = data[7];
	json["event"] = data[8];
	json["task"] = data[9];
	json["projectSettings"] = data[10];
	json["cloud"] = data[11];

	QJsonDocument doc(json);
	QByteArray jsonba = doc.toJson(QJsonDocument::Compact);
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("roles/addprojectroles")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
	QNetworkReply *request = _Manager->post(requestSend, jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::DeleteProjectRole(QVector<QString> &data)
{
	QJsonObject json;

	json["_token"] = data[0];
	json["projectId"] = data[1];
	json["roleId"] = data[2];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("roles/delprojectroles")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->sendCustomRequest(requestSend, QByteArray("DELETE"), new QBuffer(jsonba));
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;

}

QNetworkReply *DataConnectorOnline::AttachRole(QVector<QString> &data)
{
	QJsonObject json;

	json["_token"] = data[0];
	json["projectId"] = data[1];
	json["userId"] = data[2];
	json["roleId"] = data[3];

	QJsonDocument doc(json);
	QByteArray jsonba = doc.toJson(QJsonDocument::Compact);
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("roles/assignpersontorole")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
	QNetworkReply *request = _Manager->post(requestSend, jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::DetachRole(QVector<QString> &data)
{
	QJsonObject json;


	json["_token"] = data[0];
	json["projectId"] = data[1];
	json["userId"] = data[2];
	json["roleId"] = data[3];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("roles/delpersonrole")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	qDebug() << *jsonba;
	QNetworkReply *request = _Manager->sendCustomRequest(requestSend, QByteArray("DELETE"), new QBuffer(jsonba));
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::ProjectInvite(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[0];
	json["projectId"] = data[1];
	json["userEmail"] = data[2];

	QJsonDocument doc(json);
	QByteArray jsonba = doc.toJson(QJsonDocument::Compact);
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("projects/addusertoproject")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
	QNetworkReply *request = _Manager->post(requestSend, jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::DeleteProjectUser(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[0];
	json["projectId"] = data[1];
	json["userId"] = data[2];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("projects/removeusertoproject")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->sendCustomRequest(requestSend, QByteArray("DELETE"), new QBuffer(jsonba));
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::DeleteCustomerAccess(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[0];
	json["projectId"] = data[1];
	json["customerAccessId"] = data[2];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("projects/delcustomeraccess")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->sendCustomRequest(requestSend, QByteArray("DELETE"), new QBuffer(jsonba));
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::DeleteProject(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[0];
	json["projectId"] = data[1];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("projects/delproject")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->sendCustomRequest(requestSend, QByteArray("DELETE"), new QBuffer(jsonba));
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::EditBug(QVector<QString> &data)
{
	QJsonObject json;

	//data[0] = id dans l'URL
	json["token"] = data[1];
	json["title"] = data[2];
	json["description"] = data[3];
	json["stateId"] = data[4];
	json["stateName"] = data[5];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("bugtracker/editticket/") + data[0]));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::OpenBug(QVector<QString> &data)
{
	QJsonObject json;
	QString idProject = data[0];

	json["token"] = data[1];
	json["title"] = data[2];
	json["description"] = data[3];
	json["stateId"] = data[4];
	json["stateName"] = data[5];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("bugtracker/postticket/") + idProject));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::CommentBug(QVector<QString> &data)
{
	QJsonObject json;
	QString idProject = data[0];

	json["token"] = data[1];
	json["title"] = data[2];
	json["description"] = data[3];
	json["parentId"] = data[4];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("bugtracker/postcomment/") + idProject));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::CustomerGenerateAccess(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[0];
	json["projectId"] = data[1];
	json["name"] = data[2];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("projects/generatecustomeraccess")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::EditMessageTimeline(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[1];
	json["messageId"] = data[2];
	json["title"] = data[3];
	json["message"] = data[4];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("timeline/editmessage/") + data[0]));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::PostMessageTimeline(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[1];
	json["title"] = data[2];
	json["message"] = data[3];
	if (data.size() > 4)
		json["commentedId"] = data[4];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("timeline/postmessage/") + data[0]));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply * API::DataConnectorOnline::PostEvent(QVector<QString>& data)
{
	QJsonObject json;

	QJsonObject dataJson;
	dataJson["token"] = data[0];
	dataJson["projectId"] = data[1];
	dataJson["title"] = data[2];
	dataJson["description"] = data[3];
	dataJson["icon"] = data[4];
	dataJson["typeId"] = data[5];
	dataJson["begin"] = data[6];
	dataJson["end"] = data[7];

	json["data"] = dataJson;

	qDebug() << "Data : " << json;
		 
	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(URL_API + QString("event/postevent")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *API::DataConnectorOnline::EditEvent(QVector<QString>& data)
{
	QJsonObject json;

	json["data"] = QJsonObject();
	json["data"].toObject()["token"] = data[0];
	json["data"].toObject()["eventId"] = data[1];
	json["data"].toObject()["title"] = data[2];
	json["data"].toObject()["description"] = data[3];
	json["data"].toObject()["icon"] = data[4];
	json["data"].toObject()["typeId"] = data[5];
	json["data"].toObject()["begin"] = data[6];
	json["data"].toObject()["end"] = data[7];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(URL_API + QString("event/editevent")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->put(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::AssignTagToBug(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[0];
	json["bugId"] = data[1];
	json["tagId"] = data[2];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("bugtracker/assigntag")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->put(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::AssignUserToTicket(QVector<QString> &data)
{
	QJsonObject json;
	QJsonArray toAdd;
	QString bugId = data[0];

	for (int i = 2; i < data.length(); ++i)
		toAdd.append(data[i]);

	json["token"] = data[1];
	json["toRemove"] = QJsonArray();
	json["toAdd"] = toAdd;

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("bugtracker/setparticipants/") + bugId));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::DeleteUserToTicket(QVector<QString> &data)
{
	QJsonObject json;
	QJsonArray toRemove;
	QString bugId = data[0];

	for (int i = 2; i < data.length(); ++i)
		toRemove.append(data[i]);

	json["token"] = data[1];
	json["toRemove"] = toRemove;
	json["toAdd"] = QJsonArray();

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("bugtracker/setparticipants/") + bugId));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::CreateTag(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[0];
	json["projectId"] = data[1];
	json["name"] = data[2];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("bugtracker/tagcreation")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::EditCommentBug(QVector<QString> &data)
{
	QJsonObject json;

	json["projectId"] = data[0];
	json["token"] = data[1];
	json["commentId"] = data[2];
	json["title"] = data[3];
	json["description"] = data[4];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("bugtracker/editcomment")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::RESTDelete(QVector<QString> &data, QString baseURL)
{
	QString URL = baseURL;
	QVector<QString>::iterator dataIt;

	for (dataIt = data.begin(); dataIt != data.end(); ++dataIt)
		URL += "/" + *dataIt;

	QNetworkRequest requestSend(QUrl(OLD_URL_API + URL));
	QNetworkReply *request = _Manager->deleteResource(requestSend);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}
