#pragma once

#include <QString>
#include <QMap>

enum STYLE_SHEET
{
	BASE,
	DASHBOARD,
	WHITEBOARD,
	CALENDAR,
	BUG_TRACKER,
	PROJECT_SETTINGS,
	USER_SETTINGS,
	TIMELINE
};

class SStyleLoader
{
private:
	SStyleLoader();
	~SStyleLoader();

public:
	static QString LoadStyleSheet(STYLE_SHEET type);

private:
	QMap<STYLE_SHEET, QString> _StyleSheets;
};