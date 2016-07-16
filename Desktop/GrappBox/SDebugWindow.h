#pragma once
#include <QWidget>
#include <QMap>
#include <QLabel>
#include <QListWidget>
#include <QFormLayout>
#include <QElapsedTimer>
#include <QNetWorkReply>
#include <QNetworkRequest>
#include <QJsonArray>
#include <QJsonDocument>
#include <QJsonObject>
#include <QTimer>
#include <QTime>
#include <QByteArray>
#include <QTextEdit>
#include "RequestView.h"

#define REGISTER_REQUEST(req, url, data) SDebugWindow::GetInstance()->RegisterRequest(req, url, data)
#define FINISH_REQUEST(req, errorCode, errorMessage, dataOut) SDebugWindow::GetInstance()->ReturnRequest(req, errorCode, errorMessage, dataOut)
#define SET_CURRENT_FEATURE(name) SDebugWindow::GetInstance()->SetCurrentApplication(name)

class SDebugWindow : public QWidget
{
	Q_OBJECT
private:
	SDebugWindow();
	~SDebugWindow();

public:
	static SDebugWindow *GetInstance();
	static void Destroy();

    void RegisterRequest(int request, QString url, QByteArray dataSent);
    void ReturnRequest(int request, QString errorCode, QString errorMessage, QByteArray dataReceive);
	void SetCurrentApplication(QString appName);

public slots:
	void OnRegisterDoubleClick(QListWidgetItem *item);
	void OnFinishedDoubleClick(QListWidgetItem *item);

private:
	QLabel *_CurrentApp;
	QListWidget *_InTreatmentRequest;
	QListWidget *_FinishedRequest;

    QMap<int, RequestDebug*> _MapRequest;
};

