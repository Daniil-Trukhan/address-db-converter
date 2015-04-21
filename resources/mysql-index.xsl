<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="text" encoding="UTF-8"/>

    <xsl:template match="/database/table">
        <xsl:text>ALTER TABLE `</xsl:text><xsl:value-of select="@id"/><xsl:text>`</xsl:text>

		<!-- For each key, handle key type in loop -->
		<xsl:for-each select="*">
		
			<xsl:choose>
				<xsl:when test="name(current()) = 'primary-key'">
					<xsl:text> ADD PRIMARY KEY (</xsl:text><xsl:value-of select="@field"/><xsl:text>)</xsl:text>
				</xsl:when>
				<xsl:when test="name(current()) = 'foreign-key'">
					<xsl:text> ADD FOREIGN KEY (</xsl:text><xsl:value-of select="@field"/><xsl:text>)</xsl:text>
					<xsl:text> REFERENCES </xsl:text><xsl:value-of select="@for-table"/><xsl:text>(</xsl:text><xsl:value-of select="@for-field"/><xsl:text>)</xsl:text>
				</xsl:when>
			</xsl:choose>
			
			<!-- Separator -->
			<xsl:if test="position() != last()">
				<xsl:text>,</xsl:text>
			</xsl:if>
		
		</xsl:for-each>
		
		<!-- End of statement -->
		<xsl:text>;</xsl:text>

    </xsl:template>
</xsl:stylesheet>