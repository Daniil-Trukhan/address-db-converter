<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="text" encoding="UTF-8"/>

    <!-- From XSLT processor -->

    <xsl:template match="/database/table">
        <xsl:text>ALTER TABLE `</xsl:text><xsl:value-of select="@id"/><xsl:text>`</xsl:text>

        <xsl:for-each select="primary-key">
            <xsl:text> ADD PRIMARY KEY (</xsl:text>
            <xsl:value-of select="@field"/>
            <xsl:text>)</xsl:text>
        </xsl:for-each>

        <xsl:for-each select="foreign-key">
            <xsl:text> ADD FOREIGN KEY (</xsl:text>
            <xsl:value-of select="@field"/>
            <xsl:text>)</xsl:text>

            <xsl:text> REFERENCES </xsl:text>
            <xsl:value-of select="@for-table"/>
            <xsl:text>(</xsl:text>
            <xsl:value-of select="@for-column"/>
            <xsl:text>)</xsl:text>
        </xsl:for-each>
    </xsl:template>
</xsl:stylesheet>