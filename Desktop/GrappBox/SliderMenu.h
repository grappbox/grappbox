#ifndef SLIDERMENU_H
#define SLIDERMENU_H

#include <QList>
#include <QVBoxLayout>
#include <QtWidgets/QPushButton>
#include <QWidget>

class SliderMenu : public QWidget
{
    Q_OBJECT
public:
    explicit SliderMenu(QWidget *parent = 0);
    int AddMenuItem(QString name);
    QString GetMenuItem(int id);

signals:
    void MenuChanged(int);

public slots:
    void ForceChangeMenu(int menu);
    void ButtonChangeMenu();

private:
    QVBoxLayout         *_MainLayout;

private:
    QList<QPushButton*>     _ListButton;
    int                 _CurrentIndex;
};

#endif // SLIDERMENU_H
